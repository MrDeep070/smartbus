<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

session_start();

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (empty($_GET['id'])) {
    $_SESSION['error'] = 'Booking ID not provided';
    header('Location: my_bookings.php');
    exit;
}

$booking_id = $_GET['id'];

try {
    $pdo->beginTransaction();
    
    // Get booking details with FOR UPDATE lock
  $stmt = $pdo->prepare("SELECT bk.*, b.available_seats, b.seats_total, b.departure_time, b.id AS bus_id
                       FROM bookings bk
                       JOIN buses b ON bk.bus_id = b.id
                       WHERE bk.id = ? AND bk.user_id = ? FOR UPDATE");

    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        throw new Exception("Booking not found");
    }
    
    // Check if booking can be cancelled
    if ($booking['status'] !== 'confirmed') {
        throw new Exception("Booking is not confirmed");
    }
    
    $departure_time = strtotime($booking['departure_time']);
    if ($departure_time <= time()) {
        throw new Exception("Cannot cancel booking after departure time");
    }

    // Calculate cancellation fee (12% of amount paid) FIRST
    $cancellation_fee = $booking['amount_paid'] * 0.12;
    $refund_amount = $booking['amount_paid'] - $cancellation_fee;
    
    // Update booking status and store cancellation fee
    $stmt = $pdo->prepare("UPDATE bookings 
                          SET status = 'cancelled', cancellation_fee = ?
                          WHERE id = ?");
    $stmt->execute([$cancellation_fee, $booking_id]);
    
    // Release seats
    $num_seats = count(json_decode($booking['seats'], true));
    $new_available = $booking['available_seats'] + $num_seats;
    
    // Ensure available seats don't exceed total seats
    if ($new_available > $booking['seats_total']) {
        $new_available = $booking['seats_total'];
    }
    
    $stmt = $pdo->prepare("UPDATE buses SET available_seats = ? WHERE id = ?");
    $stmt->execute([$new_available, $booking['bus_id']]);
    
    // Refund to wallet (88% of amount)
    $stmt = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
    $stmt->execute([$refund_amount, $_SESSION['user_id']]);
    
    // Record wallet transaction - NOW $refund_amount is defined
    $bkid_str = str_pad($booking_id, 5, '0', STR_PAD_LEFT);
    $description = "Refund for cancelled booking BK$bkid_str";
    $stmt = $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, description)
                          VALUES (?, 'credit', ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $refund_amount, $description]);
    
    $pdo->commit();
    
    $_SESSION['success'] = "Booking cancelled successfully. ₹" . number_format($refund_amount, 2) . 
                          " refunded to your wallet (12% cancellation fee applied).";
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = $e->getMessage();
}

header('Location: my_bookings.php');
exit;
?>
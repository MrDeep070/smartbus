<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isset($_GET['id'])) {
    header("Location: bookings.php");
    exit;
}

$id = (int) $_GET['id'];

// Check booking status first
$stmt = $pdo->prepare("SELECT status FROM bookings WHERE id = ?");
$stmt->execute([$id]);
$booking = $stmt->fetch();

if (!$booking) {
    header("Location: bookings.php?error=notfound");
    exit;
}

if ($booking['status'] !== 'cancelled') {
    header("Location: bookings.php?error=notallowed");
    exit;
}

// Delete booking
$stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
$stmt->execute([$id]);

header("Location: bookings.php?success=deleted");
exit;
?>

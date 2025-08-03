<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../vendor/autoload.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (empty($_GET['id'])) {
    die('Booking ID not provided');
}

$booking_id = $_GET['id'];

// Fetch booking details
$stmt = $pdo->prepare("SELECT bk.*, u.name AS user_name, u.email, b.bus_no, b.bus_type, 
                              b.source, b.destination, b.departure_time, b.arrival_time, b.fare
                       FROM bookings bk
                       JOIN users u ON bk.user_id = u.id
                       JOIN buses b ON bk.bus_id = b.id
                       WHERE bk.id = ? AND bk.user_id = ?");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    die('Booking not found');
}

// Format dates
$departure = date('d M Y, h:i A', strtotime($booking['departure_time']));
$arrival = date('h:i A', strtotime($booking['arrival_time']));
$booking_date = date('d M Y, h:i A', strtotime($booking['booking_date']));
$seats = json_decode($booking['seats'], true);

// Generate PDF
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .ticket { border: 2px solid #000; padding: 20px; max-width: 600px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { color: #2c5282; }
        .details { margin-bottom: 20px; }
        .details-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .section-title { background-color: #f0f0f0; padding: 5px; font-weight: bold; }
        .qr-code { text-align: center; margin: 20px 0; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <h1>SmartBus Ticket</h1>
            <p>Booking ID: BK'.str_pad($booking['id'], 5, '0', STR_PAD_LEFT).'</p>
            <p>Status: '.$booking['status'].'</p>
        </div>
        
        <div class="details">
            <div class="details-row">
                <div><strong>Passenger:</strong> '.htmlspecialchars($booking['user_name']).'</div>
                <div><strong>Booking Date:</strong> '.$booking_date.'</div>
            </div>
            
            <div class="section-title">Journey Details</div>
            <div class="details-row">
                <div><strong>Bus:</strong> '.htmlspecialchars($booking['bus_no']).' ('.htmlspecialchars($booking['bus_type']).')</div>
                <div><strong>Seats:</strong> '.implode(', ', $seats).'</div>
            </div>
            <div class="details-row">
                <div><strong>From:</strong> '.htmlspecialchars($booking['source']).'</div>
                <div><strong>Departure:</strong> '.$departure.'</div>
            </div>
            <div class="details-row">
                <div><strong>To:</strong> '.htmlspecialchars($booking['destination']).'</div>
                <div><strong>Arrival:</strong> '.$arrival.'</div>
            </div>
            
            <div class="section-title">Payment Details</div>
            <div class="details-row">
                <div><strong>Amount Paid:</strong> ₹'.number_format($booking['amount_paid'], 2).'</div>
                <div><strong>Discount:</strong> ₹'.number_format($booking['discount'], 2).'</div>
            </div>
        </div>
        
        <div class="qr-code">
            [QR Code Placeholder for BK'.str_pad($booking['id'], 5, '0', STR_PAD_LEFT).']
        </div>
        
        <div class="footer">
            <p>Thank you for traveling with SmartBus!</p>
            <p>For support: support@smartbus.in | +91 1234567890</p>
        </div>
    </div>
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A5', 'portrait');
$dompdf->render();
$dompdf->stream('ticket_BK'.str_pad($booking['id'], 5, '0', STR_PAD_LEFT).'.pdf');
?>

<?php
// Calculate fare with GST
function calculateFare($base_fare, $seats, $discount = 0) {
    $gst_percent = 5;
    $subtotal = $base_fare * count($seats);
    $gst_amount = ($subtotal * $gst_percent) / 100;
    $total = $subtotal + $gst_amount - $discount;
    return [
        'subtotal' => $subtotal,
        'gst_amount' => $gst_amount,
        'discount' => $discount,
        'total' => $total
    ];
}

// Generate PDF invoice
function generateInvoice($booking_data) {
    // Implementation using dompdf/mpdf
    // Returns PDF file path
}

// Check seat availability
function isSeatAvailable($bus_id, $seat_number) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings 
                          WHERE bus_id = ? 
                          AND JSON_CONTAINS(seats, ?)");
    $stmt->execute([$bus_id, json_encode($seat_number)]);
    return $stmt->fetchColumn() === 0;
}
?>
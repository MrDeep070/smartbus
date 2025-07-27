
<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/header.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get booking ID
if (empty($_GET['id'])) {
    header('Location: my_bookings.php');
    exit;
}

$booking_id = $_GET['id'];

// Fetch booking details
$stmt = $pdo->prepare("SELECT bk.*, u.name AS user_name, u.email, b.bus_no, b.source, b.destination, 
                              b.departure_time, b.arrival_time, b.fare
                       FROM bookings bk
                       JOIN users u ON bk.user_id = u.id
                       JOIN buses b ON bk.bus_id = b.id
                       WHERE bk.id = ? AND bk.user_id = ?");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: my_bookings.php');
    exit;
}

// Format dates
$departure = date('d M Y, h:i A', strtotime($booking['departure_time']));
$arrival = date('h:i A', strtotime($booking['arrival_time']));
$duration = strtotime($booking['arrival_time']) - strtotime($booking['departure_time']);
$hours = floor($duration / 3600);
$minutes = floor(($duration % 3600) / 60);
$booking_date = date('d M Y, h:i A', strtotime($booking['booking_date']));
$seats = json_decode($booking['seats'], true);
?>

<section class="py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md overflow-hidden">
            <div class="bg-green-600 text-white py-6 px-8 text-center">
                <i class="fas fa-check-circle text-4xl mb-3"></i>
                <h2 class="text-2xl font-bold">Booking Confirmed!</h2>
                <p class="mt-1">Your booking ID: <span class="font-bold">BK<?= str_pad($booking['id'], 5, '0', STR_PAD_LEFT) ?></span></p>
            </div>
            
            <div class="p-8">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h3 class="text-xl font-bold">Journey Details</h3>
                        <p class="text-gray-600">Thank you for booking with GSRTC</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold">₹<?= number_format($booking['amount_paid'], 2) ?></p>
                        <p class="text-gray-600 text-sm">Total amount paid</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-bus text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="font-bold"><?= htmlspecialchars($booking['bus_no']) ?></h4>
                                <p class="text-gray-600"><?= htmlspecialchars($bus['bus_type'] ?? 'N/A') ?></p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-gray-600">From</p>
                                <p class="font-medium text-lg"><?= htmlspecialchars($booking['source']) ?></p>
                                <p class="text-gray-600"><?= $departure ?></p>
                            </div>
                            
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-arrow-down text-gray-600"></i>
                                </div>
                                <div>
                                    <p class="text-gray-600">Duration</p>
                                    <p><?= $hours ?>h <?= $minutes ?>m</p>
                                </div>
                            </div>
                            
                            <div>
                                <p class="text-gray-600">To</p>
                                <p class="font-medium text-lg"><?= htmlspecialchars($booking['destination']) ?></p>
                                <p class="text-gray-600"><?= $arrival ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="bg-gray-50 rounded-lg p-6">
                            <div class="flex items-center mb-6">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-user text-blue-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold">Passenger Details</h4>
                                    <p class="text-gray-600">Ticket for: <?= htmlspecialchars($booking['user_name']) ?></p>
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <p class="text-gray-600">Seats</p>
                                    <p class="font-medium"><?= implode(', ', $seats) ?></p>
                                </div>
                                
                                <div>
                                    <p class="text-gray-600">Booking Date</p>
                                    <p class="font-medium"><?= $booking_date ?></p>
                                </div>
                                
                                <div>
                                    <p class="text-gray-600">Status</p>
                                    <p class="font-medium text-green-600">Confirmed</p>
                                </div>
                                
                                <div class="mt-6">
                                    <a href="#" 
                                       class="inline-block bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-lg font-semibold">
                                        <i class="fas fa-download mr-2"></i> Download Ticket (PDF)
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 pt-6 border-t">
                    <div class="flex justify-between">
                        <div>
                            <h4 class="font-bold mb-2">Need help?</h4>
                            <p class="text-gray-600">
                                <i class="fas fa-phone-alt mr-2"></i> +91 79 2656 0000
                            </p>
                            <p class="text-gray-600">
                                <i class="fas fa-envelope mr-2"></i> support@gsrtc.in
                            </p>
                        </div>
                        <div class="text-right">
                            <a href="my_bookings.php" class="text-blue-600 hover:underline mr-4">
                                View All Bookings
                            </a>
                            <a href="index.php" class="bg-green-600 hover:bg-green-700 text-white py-2 px-6 rounded-lg font-semibold">
                                Book Another Ticket
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
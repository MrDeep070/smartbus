<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/header.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Display messages
if (isset($_SESSION['success'])) {
    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">'.$_SESSION['success'].'</div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">'.$_SESSION['error'].'</div>';
    unset($_SESSION['error']);
}

// Get user's bookings
$stmt = $pdo->prepare("SELECT bk.*, b.bus_no, b.bus_type, b.source, b.destination, b.departure_time
                       FROM bookings bk
                       JOIN buses b ON bk.bus_id = b.id
                       WHERE bk.user_id = ?
                       ORDER BY bk.booking_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>

<section class="py-12">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-8">My Bookings</h2>
        
        <?php if (empty($bookings)): ?>
            <div class="bg-white rounded-xl shadow-md p-8 text-center">
                <i class="fas fa-ticket-alt text-5xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-bold mb-2">No bookings yet</h3>
                <p class="text-gray-600 mb-6">You haven't made any bookings yet. Start your journey now!</p>
                <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg font-semibold">
                    Book a Bus
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($bookings as $booking): 
                    $departure = date('d M Y, h:i A', strtotime($booking['departure_time']));
                    $booking_date = date('d M Y, h:i A', strtotime($booking['booking_date']));
                    $seats = json_decode($booking['seats'], true);
                ?>
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row justify-between">
                            <div class="mb-4 md:mb-0">
                                <div class="flex items-center">
                                    <h3 class="text-xl font-bold"><?= htmlspecialchars($booking['bus_no']) ?></h3>
                                    <span class="ml-3 bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                        <?= htmlspecialchars($booking['bus_type']) ?>
                                    </span>
                                </div>
                                <p class="text-gray-600 mt-1">
                                    Booking ID: BK<?= str_pad($booking['id'], 5, '0', STR_PAD_LEFT) ?>
                                </p>
                            </div>
                            
                            <div class="text-center mb-4 md:mb-0">
                                <p class="text-lg font-bold">₹<?= number_format($booking['amount_paid'], 2) ?></p>
                                <p class="text-gray-600 text-sm">Amount Paid</p>
                            </div>
                            
                            <div>
                                <span class="px-2 py-1 rounded-full text-xs 
                                    <?= $booking['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : '' ?>
                                    <?= $booking['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : '' ?>
                                    <?= $booking['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' ?>">
                                    <?= ucfirst($booking['status']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <p class="text-gray-600">Route</p>
                                <p class="font-medium"><?= htmlspecialchars($booking['source']) ?> to <?= htmlspecialchars($booking['destination']) ?></p>
                            </div>
                            
                            <div>
                                <p class="text-gray-600">Departure</p>
                                <p class="font-medium"><?= $departure ?></p>
                            </div>
                            
                            <div>
                                <p class="text-gray-600">Seats</p>
                                <p class="font-medium"><?= implode(', ', $seats) ?></p>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-between">
                            <div>
                                <p class="text-gray-600">Booking Date</p>
                                <p class="font-medium"><?= $booking_date ?></p>
                            </div>
                            
                            <div class="flex space-x-3">
                                <a href="confirm.php?id=<?= $booking['id'] ?>" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg font-semibold">
                                    View Details
                                </a>
                                
                                <?php if ($booking['status'] === 'confirmed' && strtotime($booking['departure_time']) > time()): ?>
                                <a href="cancel_booking.php?id=<?= $booking['id'] ?>" 
                                   class="bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg font-semibold"
                                   onclick="return confirm('Are you sure? 12% cancellation fee will be deducted!')">
                                    Cancel Booking
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
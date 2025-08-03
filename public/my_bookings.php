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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | GSRTC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0c4da2',
                        secondary: '#f97316',
                        accent: '#0ea5e9',
                        dark: '#0f172a'
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
        }
        
        .booking-card {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border-radius: 16px;
            overflow: hidden;
            border-left: 4px solid #0c4da2;
        }
        
        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .route-badge {
            background: linear-gradient(45deg, #0c4da2, #0ea5e9);
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }
        
        .status-badge {
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .confirmed {
            background: linear-gradient(45deg, #10b981, #34d399);
            color: white;
        }
        
        .cancelled {
            background: linear-gradient(45deg, #ef4444, #f87171);
            color: white;
        }
        
        .pending {
            background: linear-gradient(45deg, #f59e0b, #fcd34d);
            color: white;
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
            margin-top: 20px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 5px;
            bottom: 5px;
            width: 2px;
            background-color: #e2e8f0;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 15px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -30px;
            top: 5px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #0c4da2;
            border: 4px solid white;
        }
        
        .no-bookings {
            background: linear-gradient(135deg, #f0f9ff 0%, #e6f7ff 100%);
            border-radius: 16px;
        }
        
        .action-btn {
            transition: all 0.3s ease;
            border-radius: 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header is included from header.php -->
    <?php require_once '../includes/header.php'; ?>
    
    <main class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">My Bookings</h1>
            <p class="text-gray-600">Manage your upcoming and past bus journeys</p>
        </div>
        
        <?php if (empty($bookings)): ?>
            <div class="no-bookings rounded-xl shadow-md p-12 text-center">
                <div class="inline-block bg-blue-100 p-6 rounded-full mb-6">
                    <i class="fas fa-ticket-alt text-4xl text-blue-600"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-3">No bookings yet</h3>
                <p class="text-gray-600 max-w-lg mx-auto mb-8">
                    You haven't made any bookings yet. Start your journey by booking your first bus trip!
                </p>
                <a href="index.php" class="bg-primary hover:bg-blue-800 text-white py-3 px-8 rounded-lg font-semibold inline-flex items-center">
                    <i class="fas fa-bus mr-2"></i> Book a Bus Now
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($bookings as $booking): 
                    $departure = date('d M Y, h:i A', strtotime($booking['departure_time']));
                    $booking_date = date('d M Y, h:i A', strtotime($booking['booking_date']));
                    $seats = json_decode($booking['seats'], true);
                    $booking_id = 'BK' . str_pad($booking['id'], 5, '0', STR_PAD_LEFT);
                ?>
                <div class="booking-card bg-white">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                            <div class="flex items-center mb-4 md:mb-0">
                                <div class="mr-4">
                                    <i class="fas fa-bus text-3xl text-primary"></i>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($booking['bus_no']) ?></h2>
                                    <div class="flex items-center mt-1">
                                        <span class="route-badge">
                                            <?= htmlspecialchars($booking['bus_type']) ?>
                                        </span>
                                        <span class="ml-2 status-badge <?= $booking['status'] ?>">
                                            <?= ucfirst($booking['status']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center md:text-right">
                                <div class="text-2xl font-bold text-primary">₹<?= number_format($booking['amount_paid'], 2) ?></div>
                                <div class="text-gray-600">Amount Paid</div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-route text-blue-600 mr-2"></i>
                                    <h3 class="font-medium text-gray-700">Route</h3>
                                </div>
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($booking['source']) ?> to <?= htmlspecialchars($booking['destination']) ?></p>
                            </div>
                            
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-clock text-blue-600 mr-2"></i>
                                    <h3 class="font-medium text-gray-700">Departure</h3>
                                </div>
                                <p class="font-semibold text-gray-800"><?= $departure ?></p>
                            </div>
                            
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-chair text-blue-600 mr-2"></i>
                                    <h3 class="font-medium text-gray-700">Seats</h3>
                                </div>
                                <p class="font-semibold text-gray-800"><?= implode(', ', $seats) ?></p>
                            </div>
                        </div>
                        
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="text-gray-600">Booking ID</div>
                                <div class="font-bold text-gray-800"><?= $booking_id ?></div>
                            </div>
                            <div class="timeline-item">
                                <div class="text-gray-600">Booking Date</div>
                                <div class="font-bold text-gray-800"><?= $booking_date ?></div>
                            </div>
                            <div class="timeline-item">
                                <div class="text-gray-600">Passenger Name</div>
                                <div class="font-bold text-gray-800"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
                            </div>
                        </div>
                        
                        <div class="mt-8 flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                            <a href="confirm.php?id=<?= $booking['id'] ?>" 
                               class="action-btn bg-primary hover:bg-blue-800 text-white py-3 px-6">
                                <i class="fas fa-file-invoice mr-2"></i> View Ticket
                            </a>
                            
                            <?php if ($booking['status'] === 'confirmed' && strtotime($booking['departure_time']) > time()): ?>
                            <a href="cancel_booking.php?id=<?= $booking['id'] ?>" 
                               class="action-btn bg-red-600 hover:bg-red-700 text-white py-3 px-6"
                               onclick="return confirm('Are you sure? 12% cancellation fee will be deducted!')">
                                <i class="fas fa-times-circle mr-2"></i> Cancel Booking
                            </a>
                            <?php endif; ?>
                            
                            <a href="#" class="action-btn bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 px-6">
                                <i class="fas fa-map-marker-alt mr-2"></i> Track Bus
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-10 flex justify-center">
                <nav class="inline-flex space-x-2">
                    <a href="#" class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <a href="#" class="bg-primary text-white py-2 px-4 rounded-lg">1</a>
                    <a href="#" class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg">2</a>
                    <a href="#" class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg">3</a>
                    <a href="#" class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </nav>
            </div>
        <?php endif; ?>
        
        <!-- Booking Tips -->
        <div class="mt-12 bg-blue-50 border border-blue-200 rounded-xl p-6">
            <div class="flex items-start">
                <div class="mr-4 text-blue-600">
                    <i class="fas fa-info-circle text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-gray-800 mb-2">Booking Information</h3>
                    <ul class="list-disc pl-5 text-gray-600 space-y-1">
                        <li>You can cancel bookings up to 4 hours before departure</li>
                        <li>Cancellations incur a 12% fee of the ticket amount</li>
                        <li>Refunds are processed within 3-5 business days</li>
                        <li>Print or download your ticket before boarding the bus</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Footer is included from footer.php -->
    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
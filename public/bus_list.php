<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/header.php';

// Validate search parameters
if (empty($_GET['source']) || empty($_GET['destination']) || empty($_GET['date'])) {
    header('Location: index.php');
    exit;
}

$source = $_GET['source'];
$destination = $_GET['destination'];
$date = $_GET['date'];
$today = date('Y-m-d');

// Validate date
if ($date < $today) {
    echo "<script>alert('Past dates not allowed!'); window.history.back();</script>";
    exit;
}

// Fetch buses
$stmt = $pdo->prepare("SELECT * FROM buses 
                       WHERE source LIKE ? 
                       AND destination LIKE ? 
                       AND date = ?
                       AND available_seats > 0
                       ORDER BY departure_time");
$stmt->execute(["%$source%", "%$destination%", $date]);
$buses = $stmt->fetchAll();
?>

<section class="py-12">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-6">Available Buses</h2>
        
        <div class="bg-white rounded-xl shadow-md mb-6 p-4">
            <p class="text-lg">
                <span class="font-semibold"><?= htmlspecialchars($source) ?></span> to 
                <span class="font-semibold"><?= htmlspecialchars($destination) ?></span> on 
                <span class="font-semibold"><?= date('D, d M Y', strtotime($date)) ?></span>
            </p>
            <p class="text-gray-600"><?= count($buses) ?> buses found</p>
        </div>

        <?php if (empty($buses)): ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 mb-6">
                <p>No buses found for your search. Try different date or route.</p>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($buses as $bus): 
                    $departure = date('h:i A', strtotime($bus['departure_time']));
                    $arrival = date('h:i A', strtotime($bus['arrival_time']));
                    $duration = strtotime($bus['arrival_time']) - strtotime($bus['departure_time']);
                    $hours = floor($duration / 3600);
                    $minutes = floor(($duration % 3600) / 60);
                ?>
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row justify-between">
                            <div class="mb-4 md:mb-0">
                                <div class="flex items-center">
                                    <h3 class="text-xl font-bold"><?= htmlspecialchars($bus['bus_no']) ?></h3>
                                    <span class="ml-3 bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                        <?= $bus['bus_type'] ?>
                                    </span>
                                </div>
                                <p class="text-gray-600 mt-1">
                                    <i class="fas fa-chair text-green-500"></i> 
                                    <?= $bus['available_seats'] ?> seats available
                                </p>
                            </div>
                            
                            <div class="text-center mb-4 md:mb-0">
                                <p class="text-2xl font-bold">₹<?= number_format($bus['fare'], 2) ?></p>
                                <p class="text-gray-600 text-sm">per seat</p>
                            </div>
                            
                            <div class="flex flex-col items-end">
                                <p class="text-lg font-semibold"><?= $departure ?></p>
                                <p class="text-gray-600 text-sm"><?= $hours ?>h <?= $minutes ?>m</p>
                                <p class="text-lg font-semibold mt-1"><?= $arrival ?></p>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-between items-center">
                            <div>
                                <p class="text-green-600 font-semibold">
                                    <i class="fas fa-check-circle"></i> Free Cancellation
                                </p>
                            </div>
                            <a href="booking.php?bus_id=<?= $bus['id'] ?>" 
                               class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-lg font-semibold">
                                Select Seats
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
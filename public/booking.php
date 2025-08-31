
<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/header.php';

// if (!isLoggedIn()) {
//     header('Location: login.php');
//     exit;
// }

// Get bus details
if (empty($_GET['bus_id'])) {
    header('Location: index.php');
    exit;
}

$bus_id = $_GET['bus_id'];
$stmt = $pdo->prepare("SELECT * FROM buses WHERE id = ?");
$stmt->execute([$bus_id]);
$bus = $stmt->fetch();

if (!$bus) {
    header('Location: bus_list.php');
    exit;
}

// Get already booked seats
$stmt = $pdo->prepare("SELECT seats FROM bookings WHERE bus_id = ? AND status = 'confirmed'");
$stmt->execute([$bus_id]);
$booked_seats = [];
while ($row = $stmt->fetch()) {
    $seats = json_decode($row['seats'], true);
    $booked_seats = array_merge($booked_seats, $seats);
}

// Seat selection logic
$selected_seats = [];
$fare = $bus['fare'];
$gst_percent = 5;
$total_amount = 0;
$gst_amount = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_seats = $_POST['seats'] ?? [];
    $num_seats = count($selected_seats);
    
    if ($num_seats > 0) {
        $subtotal = $fare * $num_seats;
        $gst_amount = ($subtotal * $gst_percent) / 100;
        $total_amount = $subtotal + $gst_amount;
    }
}
?>

<section class="py-12">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-8">Select Your Seats</h2>
        
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Seat Map -->
            <div class="lg:w-2/3">
                <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-xl font-bold"><?= htmlspecialchars($bus['bus_no']) ?></h3>
                            <p class="text-gray-600">
                                <?= htmlspecialchars($bus['source']) ?> to <?= htmlspecialchars($bus['destination']) ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold">₹<?= number_format($fare, 2) ?> per seat</p>
                            <p class="text-sm text-gray-600">+ GST applicable</p>
                        </div>
                    </div>
                    
                    <div class="border-t pt-6">
                        <h4 class="text-lg font-semibold mb-4">Select Seats (Max 6)</h4>
                        
                        <form id="seatForm" method="POST">
                            <!-- Driver Seat -->
                            <div class="flex justify-center mb-10">
                                <div class="w-16 h-16 flex items-center justify-center bg-gray-200 rounded-lg">
                                    <i class="fas fa-user-shield text-2xl text-gray-600"></i>
                                </div>
                            </div>
                            
                            <!-- Seat Grid -->
                            <div class="grid grid-cols-4 gap-4">
                                <?php for ($i = 1; $i <= $bus['seats_total']; $i++): 
                                    $is_booked = in_array($i, $booked_seats);
                                    $is_selected = in_array($i, $selected_seats);
                                ?>
                                <div class="relative">
                                    <input type="checkbox" name="seats[]" value="<?= $i ?>" id="seat<?= $i ?>" 
                                           class="hidden peer" 
                                           <?= $is_booked ? 'disabled' : '' ?>
                                           <?= $is_selected ? 'checked' : '' ?>>
                                    <label for="seat<?= $i ?>" 
                                           class="block w-full h-16 flex items-center justify-center rounded-lg border-2 cursor-pointer
                                                  <?= $is_booked ? 'bg-gray-300 border-gray-400 cursor-not-allowed' : '' ?>
                                                  <?= $is_selected ? 'bg-green-100 border-green-500' : 'bg-white border-gray-300 hover:border-blue-500' ?>">
                                        <span class="text-lg font-semibold"><?= $i ?></span>
                                        <?php if ($is_booked): ?>
                                        <span class="absolute top-0 right-0 bg-red-500 text-white text-xs px-1 rounded">Booked</span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                                <?php endfor; ?>
                            </div>
                            
                            <div class="mt-8">
                                <button type="submit" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white py-3 px-8 rounded-lg font-semibold">
                                    Update Selection
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Booking Summary -->
            <div class="lg:w-1/3">
                <div class="bg-white rounded-xl shadow-md p-6 sticky top-4">
                    <h3 class="text-xl font-bold mb-6">Booking Summary</h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span>Selected Seats:</span>
                            <span class="font-semibold">
                                <?php if (!empty($selected_seats)): ?>
                                    <?= implode(', ', $selected_seats) ?>
                                <?php else: ?>
                                    None
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span>Fare (<?= count($selected_seats) ?> x ₹<?= $fare ?>):</span>
                            <span>₹<?= number_format($fare * count($selected_seats), 2) ?></span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span>GST (<?= $gst_percent ?>%):</span>
                            <span>₹<?= number_format($gst_amount, 2) ?></span>
                        </div>
                        
                        <div class="border-t pt-4 flex justify-between text-lg font-bold">
                            <span>Total Amount:</span>
                            <span>₹<?= number_format($total_amount, 2) ?></span>
                        </div>
                        
                        <?php if (!empty($selected_seats)): ?>
                        <div class="mt-6">
                            <a href="payment.php?bus_id=<?= $bus_id ?>&seats=<?= implode(',', $selected_seats) ?>" 
                               class="block w-full bg-green-600 hover:bg-green-700 text-white text-center py-3 px-4 rounded-lg font-semibold">
                                Proceed to Payment
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>

<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/header.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Validate parameters
if (empty($_GET['bus_id']) || empty($_GET['seats'])) {
    header('Location: index.php');
    exit;
}

$bus_id = $_GET['bus_id'];
$seat_numbers = explode(',', $_GET['seats']);
$user_id = $_SESSION['user_id'];

// Fetch bus details
$stmt = $pdo->prepare("SELECT * FROM buses WHERE id = ?");
$stmt->execute([$bus_id]);
$bus = $stmt->fetch();

if (!$bus) {
    header('Location: bus_list.php');
    exit;
}

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Check seat availability
$available_seats = $bus['available_seats'];
$num_seats = count($seat_numbers);

if ($num_seats > $available_seats) {
    header('Location: booking.php?bus_id=' . $bus_id . '&error=seats_unavailable');
    exit;
}

// Calculate base fare
$base_fare = $bus['fare'];
$gst_percent = 5;
$subtotal = $base_fare * $num_seats;
$gst_amount = ($subtotal * $gst_percent) / 100;
$total_amount = $subtotal + $gst_amount;

// Handle loyalty points redemption
$points_used = 0;
$discount = 0;
$final_amount = $total_amount;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process payment
    $points_used = isset($_POST['use_points']) ? min((int)$_POST['points_to_use'], $user['loyalty_points']) : 0;
    $discount = min($points_used, $total_amount);
    $final_amount = $total_amount - $discount;
    
    // Create booking record
    $seats_json = json_encode($seat_numbers);
    
    try {
        $pdo->beginTransaction();
        
        // Create booking
        $stmt = $pdo->prepare("INSERT INTO bookings 
                              (user_id, bus_id, seats, amount_paid, gst_amount, discount, status) 
                              VALUES (?, ?, ?, ?, ?, ?, 'confirmed')");
        $stmt->execute([
            $user_id, 
            $bus_id, 
            $seats_json, 
            $final_amount,
            $gst_amount,
            $discount
        ]);
        $booking_id = $pdo->lastInsertId();
        
        // Update bus available seats
        $new_available = $bus['available_seats'] - $num_seats;
        $stmt = $pdo->prepare("UPDATE buses SET available_seats = ? WHERE id = ?");
        $stmt->execute([$new_available, $bus_id]);
        
        // Update user loyalty points
        $earned_points = floor($final_amount / 100); // 1 point per ₹100 spent
        $new_points = $user['loyalty_points'] - $points_used + $earned_points;
        
        $stmt = $pdo->prepare("UPDATE users SET loyalty_points = ? WHERE id = ?");
        $stmt->execute([$new_points, $user_id]);
        
        $pdo->commit();
        
        // Redirect to confirmation page
        header('Location: confirm.php?id=' . $booking_id);
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Payment processing failed: " . $e->getMessage();
    }
}

// Calculate loyalty points redemption options
$max_points = min($user['loyalty_points'], $total_amount);
$points_value = $max_points; // 1 point = ₹1
?>

<section class="py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold mb-6">Complete Your Payment</h2>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Payment Summary -->
                <div class="lg:w-1/2">
                    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                        <h3 class="text-xl font-bold mb-4">Booking Summary</h3>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span>Bus Number:</span>
                                <span class="font-semibold"><?= htmlspecialchars($bus['bus_no']) ?></span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span>Route:</span>
                                <span class="font-semibold">
                                    <?= htmlspecialchars($bus['source']) ?> → <?= htmlspecialchars($bus['destination']) ?>
                                </span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span>Departure:</span>
                                <span class="font-semibold">
                                    <?= date('d M Y, h:i A', strtotime($bus['departure_time'])) ?>
                                </span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span>Selected Seats:</span>
                                <span class="font-semibold"><?= implode(', ', $seat_numbers) ?></span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span>Base Fare (<?= $num_seats ?> x ₹<?= $base_fare ?>):</span>
                                <span>₹<?= number_format($subtotal, 2) ?></span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span>GST (<?= $gst_percent ?>%):</span>
                                <span>₹<?= number_format($gst_amount, 2) ?></span>
                            </div>
                            
                            <div class="flex justify-between border-t pt-4">
                                <span class="font-bold">Total Amount:</span>
                                <span class="font-bold text-lg">₹<?= number_format($total_amount, 2) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Loyalty Points -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-xl font-bold mb-4">Loyalty Points</h3>
                        
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <p class="font-medium">Your Points Balance</p>
                                <p class="text-2xl text-yellow-600 font-bold">
                                    <i class="fas fa-coins"></i> <?= $user['loyalty_points'] ?> pts
                                </p>
                                <p class="text-sm text-gray-600">1 point = ₹1 discount</p>
                            </div>
                            
                            <div class="bg-yellow-100 p-3 rounded-full">
                                <i class="fas fa-medal text-yellow-600 text-2xl"></i>
                            </div>
                        </div>
                        
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="use_points" id="use_points" 
                                           class="mr-2 h-5 w-5 text-blue-600" 
                                           onchange="togglePointsUsage()">
                                    <span class="font-medium">Use loyalty points to pay</span>
                                </label>
                            </div>
                            
                            <div id="points_section" class="hidden space-y-4">
                                <div>
                                    <label class="block text-gray-700 mb-2" for="points_to_use">
                                        Points to use (max <?= $max_points ?> pts)
                                    </label>
                                    <input type="range" id="points_to_use" name="points_to_use" 
                                           min="0" max="<?= $max_points ?>" value="0"
                                           class="w-full" oninput="updatePointsValue(this.value)">
                                    <div class="flex justify-between text-sm text-gray-600">
                                        <span>0 pts</span>
                                        <span><?= $max_points ?> pts</span>
                                    </div>
                                </div>
                                
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <p class="flex justify-between">
                                        <span>Points to use:</span>
                                        <span id="points_display">0</span> pts
                                    </p>
                                    <p class="flex justify-between font-bold mt-1">
                                        <span>Discount:</span>
                                        <span id="discount_display">₹0.00</span>
                                    </p>
                                    <p class="flex justify-between mt-2 border-t pt-2">
                                        <span>Final Amount:</span>
                                        <span class="text-green-600 font-bold" id="final_amount">
                                            ₹<?= number_format($total_amount, 2) ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="mt-6">
                                <button type="submit" 
                                        class="w-full bg-green-600 hover:bg-green-700 text-white py-3 px-4 rounded-lg font-semibold text-lg">
                                    <i class="fas fa-lock mr-2"></i> Confirm Payment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Payment Options -->
                <div class="lg:w-1/2">
                    <div class="bg-white rounded-xl shadow-md p-6 sticky top-4">
                        <h3 class="text-xl font-bold mb-6">Payment Options</h3>
                        
                        <div class="space-y-4">
                            <!-- Razorpay Payment Button (Test Mode) -->
                            <div class="border rounded-lg p-4 hover:border-blue-500 cursor-pointer">
                                <div class="flex items-center">
                                    <div class="mr-4">
                                        <img src="https://razorpay.com/assets/razorpay-glyph.svg" alt="Razorpay" class="h-10">
                                    </div>
                                    <div>
                                        <h4 class="font-bold">Credit/Debit Card</h4>
                                        <p class="text-sm text-gray-600">Visa, Mastercard, Amex, Rupay</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- UPI -->
                            <div class="border rounded-lg p-4 hover:border-blue-500 cursor-pointer">
                                <div class="flex items-center">
                                    <div class="mr-4">
                                        <i class="fas fa-mobile-alt text-3xl text-purple-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold">UPI</h4>
                                        <p class="text-sm text-gray-600">Google Pay, PhonePe, Paytm, BHIM</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Net Banking -->
                            <div class="border rounded-lg p-4 hover:border-blue-500 cursor-pointer">
                                <div class="flex items-center">
                                    <div class="mr-4">
                                        <i class="fas fa-landmark text-3xl text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold">Net Banking</h4>
                                        <p class="text-sm text-gray-600">All major Indian banks</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Wallet -->
                            <div class="border rounded-lg p-4 hover:border-blue-500 cursor-pointer">
                                <div class="flex items-center">
                                    <div class="mr-4">
                                        <i class="fas fa-wallet text-3xl text-green-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold">Wallets</h4>
                                        <p class="text-sm text-gray-600">Paytm, Mobikwik, Freecharge</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8 bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-bold mb-2">Payment Security</h4>
                            <div class="flex items-center space-x-3">
                                <div class="flex items-center text-green-600">
                                    <i class="fas fa-shield-alt mr-1"></i>
                                    <span class="text-sm">SSL Secured</span>
                                </div>
                                <div class="flex items-center text-green-600">
                                    <i class="fas fa-lock mr-1"></i>
                                    <span class="text-sm">PCI DSS Compliant</span>
                                </div>
                                <div class="flex items-center text-green-600">
                                    <i class="fas fa-user-shield mr-1"></i>
                                    <span class="text-sm">3D Secure</span>
                                </div>
                            </div>
                            <p class="mt-3 text-sm text-gray-600">
                                Your payment details are securely encrypted and processed by Razorpay. 
                                We do not store your card information.
                            </p>
                        </div>
                        
                        <div class="mt-6 text-center">
                            <img src="https://razorpay.com/assets/payments.png" alt="Payment Methods" class="mx-auto">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function togglePointsUsage() {
    const pointsSection = document.getElementById('points_section');
    const usePoints = document.getElementById('use_points').checked;
    
    if (usePoints) {
        pointsSection.classList.remove('hidden');
    } else {
        pointsSection.classList.add('hidden');
        // Reset points usage
        document.getElementById('points_to_use').value = 0;
        updatePointsValue(0);
    }
}

function updatePointsValue(points) {
    const pointsDisplay = document.getElementById('points_display');
    const discountDisplay = document.getElementById('discount_display');
    const finalAmountDisplay = document.getElementById('final_amount');
    
    // Convert points to discount (1 point = ₹1)
    const discount = Math.min(points, <?= $total_amount ?>);
    const finalAmount = <?= $total_amount ?> - discount;
    
    // Update displays
    pointsDisplay.textContent = points;
    discountDisplay.textContent = '₹' + discount.toFixed(2);
    finalAmountDisplay.textContent = '₹' + finalAmount.toFixed(2);
}
</script>

<?php require_once '../includes/footer.php'; ?>
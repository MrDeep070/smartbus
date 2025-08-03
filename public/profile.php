<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/header.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get wallet balance
$stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
$stmt->execute([$user_id]);
$wallet = $stmt->fetch();
$wallet_balance = $wallet ? $wallet['balance'] : 0.00;
?>

<section class="py-12">
    <div class="container mx-auto px-4 max-w-4xl">
        <h2 class="text-3xl font-bold mb-6">My Profile</h2>
        
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex items-center mb-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-user text-2xl text-blue-600"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold"><?= htmlspecialchars($user['name']) ?></h3>
                    <p class="text-gray-600"><?= htmlspecialchars($user['email']) ?></p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <p class="text-gray-600">Loyalty Points</p>
                    <p class="text-2xl font-bold text-blue-600">
                        <i class="fas fa-coins mr-2"></i> <?= $user['loyalty_points'] ?> pts
                    </p>
                </div>
                
                <div class="bg-green-50 p-4 rounded-lg">
                    <p class="text-gray-600">Wallet Balance</p>
                    <p class="text-2xl font-bold text-green-600">
                        <i class="fas fa-wallet mr-2"></i> ₹<?= number_format($wallet_balance, 2) ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-xl font-bold mb-4">Account Information</h3>
            <form>
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="name">Full Name</label>
                    <input type="text" id="name" value="<?= htmlspecialchars($user['name']) ?>" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="email">Email Address</label>
                    <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                </div>
                
                <div class="mt-6">
                    <a href="edit_profile.php" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-lg font-semibold">
                        Edit Profile
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
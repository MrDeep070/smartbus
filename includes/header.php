<?php
require_once '../includes/auth.php'; // Ensure auth functions are included
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GSRTC Bus Booking</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('hidden');
        }
    </script>
</head>
<body class="bg-gray-50">
    <header class="bg-blue-700 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <i class="fas fa-bus text-2xl mr-3"></i>
                <h1 class="text-2xl font-bold">GSRTC Booking</h1>
            </div>
            
            <nav>
                <ul class="flex space-x-6 items-center">
                    <li><a href="index.php" class="hover:text-blue-300"><i class="fas fa-home mr-1"></i> Home</a></li>
                    <?php if(isLoggedIn()): 
                        // Get user's wallet balance
                        $wallet_balance = 0.00;
                        $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $wallet = $stmt->fetch();
                        if ($wallet) {
                            $wallet_balance = $wallet['balance'];
                        }
                    ?>
                        <li><a href="my_bookings.php" class="hover:text-blue-300"><i class="fas fa-ticket-alt mr-1"></i> My Bookings</a></li>
                        <li><a href="wallet.php" class="hover:text-blue-300"><i class="fas fa-wallet mr-1"></i> Wallet: ₹<?= number_format($wallet_balance, 2) ?></a></li>
                        <li class="relative">
                            <button onclick="toggleDropdown()" class="flex items-center focus:outline-none">
                                <i class="fas fa-user-circle mr-1"></i>
                                <?= htmlspecialchars($_SESSION['user_name']) ?> 
                                <i class="fas fa-caret-down ml-1"></i>
                            </button>
                            <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white text-gray-800 rounded-md shadow-lg py-1 z-50">
                                <div class="px-4 py-2 border-b">
                                    <p class="font-semibold"><?= htmlspecialchars($_SESSION['user_name']) ?></p>
                                    <p class="text-sm">Loyalty: <?= $_SESSION['loyalty_points'] ?? 0 ?> pts</p>
                                    <p class="text-sm">Wallet: ₹<?= number_format($wallet_balance, 2) ?></p>
                                </div>
                                <a href="profile.php" class="block px-4 py-2 hover:bg-gray-100">Profile</a>
                                <a href="wallet.php" class="block px-4 py-2 hover:bg-gray-100">Wallet</a>
                                <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100">Logout</a>
                            </div>
                        </li>
                    <?php else: ?>
                        <li><a href="login.php" class="hover:text-blue-300"><i class="fas fa-sign-in-alt mr-1"></i> Login</a></li>
                        <li><a href="register.php" class="hover:text-blue-300"><i class="fas fa-user-plus mr-1"></i> Register</a></li>
                    <?php endif; ?>
                    <?php if(isAdmin()): ?>
                        <li><a href="admin/dashboard.php" class="bg-yellow-500 px-3 py-1 rounded hover:bg-yellow-600"><i class="fas fa-lock mr-1"></i> Admin Panel</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container mx-auto px-4 py-8">
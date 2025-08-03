<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GSRTC Bus Booking</title>
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
        
        .header-gradient {
            background: linear-gradient(135deg, #0c4da2 0%, #0ea5e9 100%);
        }
        
        .nav-link {
            position: relative;
            padding-bottom: 5px;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: white;
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .mobile-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        
        .mobile-menu.active {
            max-height: 500px;
        }
        
        .profile-dropdown {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border-radius: 12px;
            overflow: hidden;
        }
        
        .admin-badge {
            background: linear-gradient(45deg, #f97316, #f59e0b);
            border-radius: 20px;
        }
        
        .wallet-badge {
            background: linear-gradient(45deg, #10b981, #34d399);
            border-radius: 20px;
        }
        
        .loyalty-badge {
            background: linear-gradient(45deg, #8b5cf6, #a78bfa);
            border-radius: 20px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Enhanced Header -->
    <header class="header-gradient text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                <!-- Logo/Brand -->
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center mr-3">
                        <i class="fas fa-bus text-primary text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl md:text-2xl font-bold">GSRTC Booking</h1>
                        <p class="text-xs opacity-80 hidden md:block">Gujarat State Road Transport</p>
                    </div>
                </div>
                
                <!-- Desktop Navigation -->
                <nav class="hidden md:flex space-x-6 items-center">
                    <a href="index.php" class="nav-link flex items-center">
                        <i class="fas fa-home mr-2"></i> Home
                    </a>
                    
                    <?php if(isLoggedIn()): 
                        $wallet_balance = 0.00;
                        $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $wallet = $stmt->fetch();
                        if ($wallet) {
                            $wallet_balance = $wallet['balance'];
                        }
                    ?>
                        <a href="my_bookings.php" class="nav-link flex items-center">
                            <i class="fas fa-ticket-alt mr-2"></i> My Bookings
                        </a>
                        
                        <a href="wallet.php" class="flex items-center bg-white/20 px-3 py-1 rounded-lg hover:bg-white/30 transition duration-300">
                            <i class="fas fa-wallet mr-2"></i>
                            <span class="font-medium">₹<?= number_format($wallet_balance, 2) ?></span>
                        </a>
                        
                        <div class="relative">
                            <button onclick="toggleDropdown()" class="flex items-center space-x-2 focus:outline-none">
                                <div class="w-8 h-8 rounded-full bg-accent flex items-center justify-center">
                                    <span class="font-bold"><?= substr(htmlspecialchars($_SESSION['user_name']), 0, 1) ?></span>
                                </div>
                                <span class="font-medium"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            
                            <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-64 bg-white text-gray-800 profile-dropdown z-50">
                                <div class="p-4 border-b">
                                    <div class="flex items-center mb-3">
                                        <div class="w-12 h-12 rounded-full bg-accent flex items-center justify-center text-white font-bold text-lg">
                                            <?= substr(htmlspecialchars($_SESSION['user_name']), 0, 1) ?>
                                        </div>
                                    <div class="ml-3">
    <h3 class="font-bold"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></h3>

</div>

                                    </div>
                                    
                                    <div class="flex space-x-2">
                                        <span class="wallet-badge text-white text-xs px-2 py-1">
                                            <i class="fas fa-wallet mr-1"></i> ₹<?= number_format($wallet_balance, 2) ?>
                                        </span>
                                        <span class="loyalty-badge text-white text-xs px-2 py-1">
                                            <i class="fas fa-star mr-1"></i> <?= $_SESSION['loyalty_points'] ?? 0 ?> pts
                                        </span>
                                    </div>
                                </div>
                                
                                <a href="profile.php" class="flex items-center px-4 py-3 hover:bg-gray-100 transition">
                                    <i class="fas fa-user-circle mr-3 text-gray-500"></i>
                                    <span>Profile Settings</span>
                                </a>
                                <a href="wallet.php" class="flex items-center px-4 py-3 hover:bg-gray-100 transition">
                                    <i class="fas fa-credit-card mr-3 text-gray-500"></i>
                                    <span>Wallet Management</span>
                                </a>
                                <a href="logout.php" class="flex items-center px-4 py-3 hover:bg-gray-100 transition text-red-500">
                                    <i class="fas fa-sign-out-alt mr-3"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="nav-link flex items-center">
                            <i class="fas fa-sign-in-alt mr-2"></i> Login
                        </a>
                        <a href="register.php" class="bg-white text-primary px-4 py-1.5 rounded-lg font-medium hover:bg-gray-100 transition">
                            <i class="fas fa-user-plus mr-1"></i> Register
                        </a>
                    <?php endif; ?>
                    
                    <?php if(isAdmin()): ?>
                        <a href="admin/dashboard.php" class="admin-badge flex items-center text-white px-3 py-1.5 text-sm">
                            <i class="fas fa-lock mr-1"></i> Admin Panel
                        </a>
                    <?php endif; ?>
                </nav>
                
                <!-- Mobile menu toggle -->
                <button class="md:hidden text-white text-xl" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <!-- Mobile Navigation -->
            <div class="mobile-menu md:hidden" id="mobile-menu">
                <div class="pt-2 pb-4 space-y-2">
                    <a href="index.php" class="block py-2 px-4 rounded hover:bg-blue-600 transition">
                        <i class="fas fa-home mr-3"></i> Home
                    </a>
                    
                    <?php if(isLoggedIn()): ?>
                        <a href="my_bookings.php" class="block py-2 px-4 rounded hover:bg-blue-600 transition">
                            <i class="fas fa-ticket-alt mr-3"></i> My Bookings
                        </a>
                        <a href="wallet.php" class="block py-2 px-4 rounded hover:bg-blue-600 transition">
                            <i class="fas fa-wallet mr-3"></i> Wallet: ₹<?= number_format($wallet_balance, 2) ?>
                        </a>
                        <a href="profile.php" class="block py-2 px-4 rounded hover:bg-blue-600 transition">
                            <i class="fas fa-user-circle mr-3"></i> Profile
                        </a>
                        <a href="logout.php" class="block py-2 px-4 rounded hover:bg-blue-600 transition">
                            <i class="fas fa-sign-out-alt mr-3"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="block py-2 px-4 rounded hover:bg-blue-600 transition">
                            <i class="fas fa-sign-in-alt mr-3"></i> Login
                        </a>
                        <a href="register.php" class="block py-2 px-4 rounded hover:bg-blue-600 transition">
                            <i class="fas fa-user-plus mr-3"></i> Register
                        </a>
                    <?php endif; ?>
                    
                    <?php if(isAdmin()): ?>
                        <a href="admin/dashboard.php" class="block py-2 px-4 rounded bg-yellow-500 hover:bg-yellow-600 transition">
                            <i class="fas fa-lock mr-3"></i> Admin Panel
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>



    <script>
        // Toggle profile dropdown
        function toggleDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('hidden');
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('profileDropdown');
            const profileBtn = document.querySelector('[onclick="toggleDropdown()"]');
            
            if (!profileBtn.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
        
        // Mobile menu toggle
        document.getElementById('menu-toggle').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('active');
        });
    </script>
</body>
</html>
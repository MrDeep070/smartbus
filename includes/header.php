
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GSRTC Bus Booking</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <header class="bg-blue-700 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <i class="fas fa-bus text-2xl mr-3"></i>
                <h1 class="text-2xl font-bold">GSRTC Booking</h1>
            </div>
            
            <nav>
                <ul class="flex space-x-6">
                    <li><a href="index.php" class="hover:text-blue-300"><i class="fas fa-home mr-1"></i> Home</a></li>
                    <?php if(isLoggedIn()): ?>
                        <li><a href="my_bookings.php" class="hover:text-blue-300"><i class="fas fa-ticket-alt mr-1"></i> My Bookings</a></li>
                        <li><a href="logout.php" class="hover:text-blue-300"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a></li>
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

<?php

require_once('../includes/db.php');

require_once('../includes/auth.php');

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}



// Get stats
$users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$buses = $pdo->query("SELECT COUNT(*) FROM buses")->fetchColumn();
$bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$revenue = $pdo->query("SELECT SUM(amount_paid) FROM bookings")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | GSRTC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Admin Header -->
    <header class="bg-gray-800 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <i class="fas fa-lock text-xl mr-3"></i>
                <h1 class="text-xl font-bold">GSRTC Admin Panel</h1>
            </div>
            
            <nav>
                <ul class="flex space-x-6">
                    <li><a href="dashboard.php" class="font-bold">Dashboard</a></li>
                    <li><a href="buses.php">Buses</a></li>
                    <li><a href="bookings.php">Bookings</a></li>
                    <li><a href="users.php">Users</a></li>
                    <li><a href="../../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <h2 class="text-2xl font-bold mb-8">Dashboard Overview</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-600">Total Users</p>
                        <p class="text-3xl font-bold mt-2"><?= $users ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-users text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-600">Active Buses</p>
                        <p class="text-3xl font-bold mt-2"><?= $buses ?></p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-bus text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-600">Total Bookings</p>
                        <p class="text-3xl font-bold mt-2"><?= $bookings ?></p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-ticket-alt text-yellow-600 text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-600">Total Revenue</p>
                        <p class="text-3xl font-bold mt-2">₹<?= number_format($revenue, 2) ?></p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-rupee-sign text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Bookings -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-xl font-bold mb-6">Recent Bookings</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2 text-left">Booking ID</th>
                                <th class="py-2 text-left">User</th>
                                <th class="py-2 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT b.id, u.name, b.amount_paid 
                                                FROM bookings b
                                                JOIN users u ON b.user_id = u.id
                                                ORDER BY b.booking_date DESC
                                                LIMIT 5");
                            while ($row = $stmt->fetch()):
                            ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3">BK<?= str_pad($row['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                <td class="py-3"><?= htmlspecialchars($row['name']) ?></td>
                                <td class="py-3 text-right">₹<?= number_format($row['amount_paid'], 2) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-right">
                    <a href="bookings.php" class="text-blue-600 hover:underline">View All Bookings</a>
                </div>
            </div>
            
            <!-- Bus Management -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold">Bus Management</h3>
                    <a href="add_bus.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-plus mr-1"></i> Add Bus
                    </a>
                </div>
                <div class="space-y-4">
                    <?php
                    $stmt = $pdo->query("SELECT * FROM buses ORDER BY date DESC LIMIT 3");
                    while ($bus = $stmt->fetch()):
                    ?>
                    <div class="border rounded-lg p-4 hover:bg-gray-50">
                        <div class="flex justify-between">
                            <h4 class="font-bold"><?= $bus['bus_no'] ?></h4>
                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                <?= $bus['bus_type'] ?>
                            </span>
                        </div>
                        <p class="text-gray-600 text-sm mt-1">
                            <?= $bus['source'] ?> to <?= $bus['destination'] ?>
                        </p>
                        <p class="text-sm mt-2">
                            <i class="far fa-calendar mr-1"></i> 
                            <?= date('d M Y', strtotime($bus['date'])) ?>
                        </p>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
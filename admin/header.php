<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../admin/login.php");
    exit();
}
?>
<header class="bg-gray-800 text-white shadow-md">
    <div class="container mx-auto px-4 py-4 flex justify-between items-center">
        <div class="text-xl font-bold flex items-center gap-2">
            <i class="fas fa-shield-alt text-yellow-400"></i>
            GSRTC Admin Panel
        </div>
        <nav>
            <ul class="flex space-x-6 text-sm font-medium">
                <li><a href="../admin/dashboard.php" class="hover:underline">Dashboard</a></li>
                <li><a href="../admin/buses.php" class="hover:underline">Buses</a></li>
                <li><a href="../admin/bookings.php" class="hover:underline">Bookings</a></li>
                <li><a href="../admin/users.php" class="hover:underline">Users</a></li>
                <li><a href="../logout.php" class="hover:underline text-red-400">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>


<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';



// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 15;
$start = ($page > 1) ? ($page - 1) * $perPage : 0;

// Search filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

// Build query
$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(u.name LIKE ? OR u.email LIKE ? OR b.bus_no LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status) && in_array($status, ['confirmed', 'cancelled', 'pending'])) {
    $where[] = "bk.status = ?";
    $params[] = $status;
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total bookings
$stmt = $pdo->prepare("SELECT COUNT(*) 
                       FROM bookings bk
                       JOIN users u ON bk.user_id = u.id
                       JOIN buses b ON bk.bus_id = b.id
                       $whereClause");
$stmt->execute($params);
$total = $stmt->fetchColumn();
$pages = ceil($total / $perPage);

// Get bookings
$stmt = $pdo->prepare("SELECT bk.*, u.name AS user_name, u.email, b.bus_no, b.source, b.destination, b.departure_time
                       FROM bookings bk
                       JOIN users u ON bk.user_id = u.id
                       JOIN buses b ON bk.bus_id = b.id
                       $whereClause
                       ORDER BY bk.booking_date DESC
                       LIMIT $start, $perPage");
$stmt->execute($params);
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include_once './header.php'; ?>
    
    <main class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold">Manage Bookings</h2>
            <div class="flex items-center space-x-4">
                <form method="GET" class="flex items-center">
                    <input type="text" name="search" placeholder="Search..." 
                           value="<?= htmlspecialchars($search) ?>"
                           class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <select name="status" class="ml-2 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    </select>
                    <button type="submit" class="ml-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="py-3 px-4 text-left">Booking ID</th>
                        <th class="py-3 px-4 text-left">User</th>
                        <th class="py-3 px-4 text-left">Bus</th>
                        <th class="py-3 px-4 text-left">Route</th>
                        <th class="py-3 px-4 text-left">Date</th>
                        <th class="py-3 px-4 text-left">Seats</th>
                        <th class="py-3 px-4 text-left">Amount</th>
                        <th class="py-3 px-4 text-left">Status</th>
                        <th class="py-3 px-4 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): 
                        $seats = json_decode($booking['seats'], true);
                        $departure = date('d M Y, h:i A', strtotime($booking['departure_time']));
                    ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4">BK<?= str_pad($booking['id'], 5, '0', STR_PAD_LEFT) ?></td>
                        <td class="py-3 px-4">
                            <div class="font-medium"><?= htmlspecialchars($booking['user_name']) ?></div>
                            <div class="text-sm text-gray-600"><?= htmlspecialchars($booking['email']) ?></div>
                        </td>
                        <td class="py-3 px-4 font-medium"><?= htmlspecialchars($booking['bus_no']) ?></td>
                        <td class="py-3 px-4">
                            <div><?= htmlspecialchars($booking['source']) ?> to <?= htmlspecialchars($booking['destination']) ?></div>
                            <div class="text-sm"><?= $departure ?></div>
                        </td>
                        <td class="py-3 px-4"><?= date('d M Y', strtotime($booking['booking_date'])) ?></td>
                        <td class="py-3 px-4"><?= implode(', ', $seats) ?></td>
                        <td class="py-3 px-4">₹<?= number_format($booking['amount_paid'], 2) ?></td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 rounded-full text-xs 
                                <?= $booking['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : '' ?>
                                <?= $booking['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : '' ?>
                                <?= $booking['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' ?>">
                                <?= ucfirst($booking['status']) ?>
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <a href="#" class="text-blue-600 hover:text-blue-800 mr-2">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="#" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="9" class="py-4 px-4 text-center text-gray-500">
                            No bookings found
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <?php if ($pages > 1): ?>
            <div class="px-4 py-3 border-t">
                <div class="flex justify-between items-center">
                    <div>
                        Showing <?= $start + 1 ?> to <?= min($start + $perPage, $total) ?> of <?= $total ?> bookings
                    </div>
                    <div class="flex space-x-1">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>" 
                               class="px-3 py-1 border rounded hover:bg-gray-100">
                                Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $pages; $i++): ?>
                            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>" 
                               class="px-3 py-1 border rounded <?= $i === $page ? 'bg-blue-600 text-white' : 'hover:bg-gray-100' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $pages): ?>
                            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>" 
                               class="px-3 py-1 border rounded hover:bg-gray-100">
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
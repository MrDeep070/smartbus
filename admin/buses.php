
<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';


// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 15;
$start = ($page > 1) ? ($page - 1) * $perPage : 0;

// Search filter
$search = $_GET['search'] ?? '';

// Build query
$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(bus_no LIKE ? OR source LIKE ? OR destination LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total buses
$stmt = $pdo->prepare("SELECT COUNT(*) FROM buses $whereClause");
$stmt->execute($params);
$total = $stmt->fetchColumn();
$pages = ceil($total / $perPage);

// Get buses
$stmt = $pdo->prepare("SELECT * FROM buses $whereClause ORDER BY date DESC, departure_time ASC LIMIT $start, $perPage");
$stmt->execute($params);
$buses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Buses | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include_once './header.php'; ?>
    
    <main class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold">Manage Buses</h2>
            <div class="flex items-center space-x-4">
                <a href="add_bus.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus mr-1"></i> Add Bus
                </a>
                <form method="GET">
                    <input type="text" name="search" placeholder="Search buses..." 
                           value="<?= htmlspecialchars($search) ?>"
                           class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                        <th class="py-3 px-4 text-left">Bus No</th>
                        <th class="py-3 px-4 text-left">Type</th>
                        <th class="py-3 px-4 text-left">Route</th>
                        <th class="py-3 px-4 text-left">Schedule</th>
                        <th class="py-3 px-4 text-left">Fare</th>
                        <th class="py-3 px-4 text-left">Seats</th>
                        <th class="py-3 px-4 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($buses as $bus): 
                        $departure = date('d M Y, h:i A', strtotime($bus['departure_time']));
                        $arrival = date('h:i A', strtotime($bus['arrival_time']));
                    ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4 font-medium"><?= htmlspecialchars($bus['bus_no']) ?></td>
                        <td class="py-3 px-4">
                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                <?= $bus['bus_type'] ?>
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div><?= htmlspecialchars($bus['source']) ?> to <?= htmlspecialchars($bus['destination']) ?></div>
                            <div class="text-sm text-gray-600">
                                <?= $departure ?> → <?= $arrival ?>
                            </div>
                        </td>
                        <td class="py-3 px-4"><?= date('d M Y', strtotime($bus['date'])) ?></td>
                        <td class="py-3 px-4">₹<?= number_format($bus['fare'], 2) ?></td>
                        <td class="py-3 px-4">
                            <div class="flex items-center">
                                <div class="w-24 bg-gray-200 rounded-full h-2.5 mr-2">
                                    <div class="bg-green-600 h-2.5 rounded-full" 
                                         style="width: <?= ($bus['available_seats'] / $bus['seats_total']) * 100 ?>%"></div>
                                </div>
                                <span><?= $bus['available_seats'] ?>/<?= $bus['seats_total'] ?></span>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <a href="edit_bus.php?id=<?= $bus['id'] ?>" class="text-blue-600 hover:text-blue-800 mr-2">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete_bus.php?id=<?= $bus['id'] ?>" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($buses)): ?>
                    <tr>
                        <td colspan="7" class="py-4 px-4 text-center text-gray-500">
                            No buses found
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
                        Showing <?= $start + 1 ?> to <?= min($start + $perPage, $total) ?> of <?= $total ?> buses
                    </div>
                    <div class="flex space-x-1">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" 
                               class="px-3 py-1 border rounded hover:bg-gray-100">
                                Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $pages; $i++): ?>
                            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" 
                               class="px-3 py-1 border rounded <?= $i === $page ? 'bg-blue-600 text-white' : 'hover:bg-gray-100' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $pages): ?>
                            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" 
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
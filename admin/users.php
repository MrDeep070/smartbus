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
    $where[] = "(name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total users
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users $whereClause");
$stmt->execute($params);
$total = $stmt->fetchColumn();
$pages = ceil($total / $perPage);

// Get users
$stmt = $pdo->prepare("SELECT * FROM users $whereClause ORDER BY created_at DESC LIMIT $start, $perPage");
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include_once './header.php'; ?>
    
    <main class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold">Manage Users</h2>
            <form method="GET">
                <input type="text" name="search" placeholder="Search users..." 
                       value="<?= htmlspecialchars($search) ?>"
                       class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="ml-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="py-3 px-4 text-left">ID</th>
                        <th class="py-3 px-4 text-left">Name</th>
                        <th class="py-3 px-4 text-left">Email</th>
                        <th class="py-3 px-4 text-left">Joined</th>
                        <th class="py-3 px-4 text-left">Loyalty Points</th>
                        <th class="py-3 px-4 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4"><?= $user['id'] ?></td>
                        <td class="py-3 px-4 font-medium"><?= htmlspecialchars($user['name']) ?></td>
                        <td class="py-3 px-4"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="py-3 px-4"><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                        <td class="py-3 px-4">
                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-sm">
                                <?= $user['loyalty_points'] ?> pts
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
                    
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="py-4 px-4 text-center text-gray-500">
                            No users found
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
                        Showing <?= $start + 1 ?> to <?= min($start + $perPage, $total) ?> of <?= $total ?> users
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

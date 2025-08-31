<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';



// Get bus ID
if (empty($_GET['id'])) {
    header('Location: buses.php');
    exit;
}

$bus_id = $_GET['id'];

// Fetch bus data
$stmt = $pdo->prepare("SELECT * FROM buses WHERE id = ?");
$stmt->execute([$bus_id]);
$bus = $stmt->fetch();

if (!$bus) {
    header('Location: buses.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bus_no = trim($_POST['bus_no']);
    $bus_type = $_POST['bus_type'];
    $source = trim($_POST['source']);
    $destination = trim($_POST['destination']);
    $departure_time = $_POST['departure_time'];
    $arrival_time = $_POST['arrival_time'];
    $fare = (float)$_POST['fare'];
    $seats_total = (int)$_POST['seats_total'];
    $date = $_POST['date'];
    
    // Calculate available seats
    $new_available_seats = $bus['available_seats'] + ($seats_total - $bus['seats_total']);
    
    // Basic validation
    if (empty($bus_no) || empty($source) || empty($destination) || $fare <= 0 || $seats_total <= 0) {
        $error = 'Please fill all required fields with valid values';
    } elseif ($departure_time >= $arrival_time) {
        $error = 'Arrival time must be after departure time';
    } elseif ($new_available_seats < 0) {
        $error = 'Cannot reduce total seats below booked seats';
    } else {
        // Update bus
        $stmt = $pdo->prepare("UPDATE buses 
                              SET bus_no = ?, bus_type = ?, source = ?, destination = ?, 
                                  departure_time = ?, arrival_time = ?, fare = ?, 
                                  seats_total = ?, available_seats = ?, date = ?
                              WHERE id = ?");
        if ($stmt->execute([$bus_no, $bus_type, $source, $destination, $departure_time, 
                           $arrival_time, $fare, $seats_total, $new_available_seats, $date, $bus_id])) {
            $success = 'Bus updated successfully!';
            // Refresh bus data
            $stmt = $pdo->prepare("SELECT * FROM buses WHERE id = ?");
            $stmt->execute([$bus_id]);
            $bus = $stmt->fetch();
        } else {
            $error = 'Failed to update bus. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Bus | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include_once './header.php'; ?>
    
    <main class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold">Edit Bus: <?= htmlspecialchars($bus['bus_no']) ?></h2>
                <a href="buses.php" class="text-blue-600 hover:underline">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Buses
                </a>
            </div>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <?= $success ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-xl shadow-md p-6">
                <form method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 mb-2" for="bus_no">Bus Number *</label>
                            <input type="text" id="bus_no" name="bus_no" required 
                                   value="<?= htmlspecialchars($bus['bus_no']) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2" for="bus_type">Bus Type *</label>
                            <select id="bus_type" name="bus_type" required 
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="AC" <?= $bus['bus_type'] === 'AC' ? 'selected' : '' ?>>AC</option>
                                <option value="Non-AC" <?= $bus['bus_type'] === 'Non-AC' ? 'selected' : '' ?>>Non-AC</option>
                                <option value="Sleeper" <?= $bus['bus_type'] === 'Sleeper' ? 'selected' : '' ?>>Sleeper</option>
                                <option value="Express" <?= $bus['bus_type'] === 'Express' ? 'selected' : '' ?>>Express</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2" for="source">Source *</label>
                            <input type="text" id="source" name="source" required 
                                   value="<?= htmlspecialchars($bus['source']) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2" for="destination">Destination *</label>
                            <input type="text" id="destination" name="destination" required 
                                   value="<?= htmlspecialchars($bus['destination']) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2" for="departure_time">Departure Time *</label>
                            <input type="datetime-local" id="departure_time" name="departure_time" required 
                                   value="<?= date('Y-m-d\TH:i', strtotime($bus['departure_time'])) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2" for="arrival_time">Arrival Time *</label>
                            <input type="datetime-local" id="arrival_time" name="arrival_time" required 
                                   value="<?= date('Y-m-d\TH:i', strtotime($bus['arrival_time'])) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2" for="fare">Fare (₹) *</label>
                            <input type="number" step="0.01" min="1" id="fare" name="fare" required 
                                   value="<?= $bus['fare'] ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2" for="seats_total">Total Seats *</label>
                            <input type="number" min="1" id="seats_total" name="seats_total" required 
                                   value="<?= $bus['seats_total'] ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-sm text-gray-500 mt-1">
                                Currently available: <?= $bus['available_seats'] ?> seats
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2" for="date">Date of Operation *</label>
                            <input type="date" id="date" name="date" required 
                                   value="<?= $bus['date'] ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div class="mt-8">
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white py-3 px-8 rounded-lg font-semibold">
                            Update Bus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>

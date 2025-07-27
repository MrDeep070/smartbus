
<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/header.php';

$stmt = $pdo->query("
    SELECT b.source, b.destination, COUNT(*) as bookings
    FROM bookings bk
    JOIN buses b ON bk.bus_id = b.id
    GROUP BY b.source, b.destination
    ORDER BY bookings DESC
    LIMIT 4
");
$popular_routes = $stmt->fetchAll();
?>


<section class="bg-blue-800 text-white py-16">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-6">Book Bus Tickets in Minutes</h1>
        <p class="text-xl mb-10 max-w-2xl mx-auto">Travel across Gujarat with GSRTC - Safe, Affordable, Reliable</p>
        
        <div class="bg-white rounded-xl shadow-xl p-6 max-w-4xl mx-auto">
            <form action="bus_list.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-gray-700 mb-2">From</label>
                    <input type="text" name="source" required 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Departure City">
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-2">To</label>
                    <input type="text" name="destination" required 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Arrival City">
                </div>
                
                <div>
    <label class="block text-gray-700 mb-2">Date</label>
    <input type="date" name="date" required 
           class="w-full px-4 py-2 border bg-blue-500 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
</div>

                
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 font-semibold">
                        Search Buses
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<section class="py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Popular Routes</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($popular_routes as $route): ?>
            <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">
                                <?= htmlspecialchars($route['source']) ?> to <?= htmlspecialchars($route['destination']) ?>
                            </h3>
                            <p class="text-gray-600 mt-2"><?= $route['bookings'] ?>+ bookings daily</p>
                        </div>
                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Popular</span>
                    </div>
                    <a href="bus_list.php?source=<?= urlencode($route['source']) ?>&destination=<?= urlencode($route['destination']) ?>&date=<?= date('Y-m-d') ?>" 
                       class="mt-4 inline-block text-blue-600 hover:underline">
                        Book Now
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="bg-gray-100 py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Why Choose GSRTC?</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-xl shadow-md text-center">
                <div class="text-blue-600 text-4xl mb-4">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Safe Travel</h3>
                <p class="text-gray-600">GPS tracked buses with trained drivers and safety protocols</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-md text-center">
                <div class="text-blue-600 text-4xl mb-4">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Affordable Fares</h3>
                <p class="text-gray-600">Lowest prices with no hidden charges</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-md text-center">
                <div class="text-blue-600 text-4xl mb-4">
                    <i class="fas fa-bus"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">3000+ Buses</h3>
                <p class="text-gray-600">Largest fleet connecting every corner of Gujarat</p>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
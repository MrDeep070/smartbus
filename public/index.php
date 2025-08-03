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
        
        .hero-bg {
            background: linear-gradient(135deg, #0c4da2 0%, #0ea5e9 100%);
        }
        
        .route-card {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border-radius: 12px;
            overflow: hidden;
        }
        
        .route-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .feature-card {
            transition: all 0.3s ease;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .search-form {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border-radius: 16px;
            overflow: hidden;
        }
        
        .input-field {
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
        }
        
        .input-field:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.2);
        }
        
        .popular-badge {
            position: absolute;
            top: -10px;
            right: 10px;
            background: linear-gradient(45deg, #f97316, #f59e0b);
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .bus-icon {
            background: linear-gradient(135deg, #0ea5e9 0%, #0c4da2 100%);
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header is included from header.php -->
    <?php require_once '../includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero-bg text-white py-16 md:py-24">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-6 leading-tight">
                    Book Bus Tickets in <span class="text-secondary">Minutes</span>
                </h1>
                <p class="text-xl mb-10 max-w-2xl mx-auto opacity-90">
                    Travel across Gujarat with GSRTC - Safe, Affordable, Reliable
                </p>
                
                <div class="search-form bg-white p-6 md:p-8 max-w-4xl mx-auto">
                    <form action="bus_list.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium">From</label>
                            <div class="relative">
                                <i class="fas fa-map-marker-alt absolute left-3 top-3 text-gray-400"></i>
                                <input type="text" name="source" required 
                                    class="w-full pl-10 pr-4 py-3 input-field focus:outline-none"
                                    placeholder="Departure City">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium">To</label>
                            <div class="relative">
                                <i class="fas fa-map-marker-alt absolute left-3 top-3 text-gray-400"></i>
                                <input type="text" name="destination" required 
                                    class="w-full pl-10 pr-4 py-3 input-field focus:outline-none"
                                    placeholder="Arrival City">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium">Date</label>
                            <div class="relative">
                                <i class="fas fa-calendar-day absolute left-3 top-3 text-gray-400"></i>
                                <input type="date" name="date" required 
                                    class="w-full pl-10 pr-4 py-3 text-black input-field focus:outline-none">
                            </div>
                        </div>
                        
                        <div class="flex items-end">
                            <button type="submit" 
                                    class="w-full bg-secondary hover:bg-orange-700 text-white py-3 px-4 rounded-lg font-bold transition duration-300 flex items-center justify-center pulse">
                                <i class="fas fa-search mr-2"></i>Search Buses
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Routes Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-14">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Popular Routes</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Discover the most frequently booked routes across Gujarat</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($popular_routes as $route): ?>
                <div class="route-card bg-white">
                    <div class="relative">
                        <div class="h-40 bg-gradient-to-r from-primary to-accent flex items-center justify-center">
                            <div class="text-white text-center px-4">
                                <i class="fas fa-route text-4xl mb-3"></i>
                                <h3 class="text-xl font-bold"><?= htmlspecialchars($route['source']) ?> to <?= htmlspecialchars($route['destination']) ?></h3>
                            </div>
                        </div>
                        <div class="popular-badge text-white">Popular</div>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-users mr-2"></i>
                                <span><?= $route['bookings'] ?>+ bookings daily</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-clock mr-2"></i>
                                <span>4h 30m</span>
                            </div>
                        </div>
                        <div class="mb-4 flex justify-between items-center">
                            <span class="text-gray-600">Fare: ₹350 - ₹650</span>
                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">45+ daily buses</span>
                        </div>
                        <a href="bus_list.php?source=<?= urlencode($route['source']) ?>&destination=<?= urlencode($route['destination']) ?>&date=<?= date('Y-m-d') ?>" 
                            class="mt-2 block w-full bg-primary hover:bg-blue-800 text-white text-center py-2 rounded-lg font-semibold transition duration-300">
                            Book Now
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-12">
                <a href="#" class="inline-flex items-center text-primary font-medium hover:text-blue-800">
                    View all routes <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Why Choose GSRTC -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-14">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Why Choose GSRTC?</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Experience the best in bus travel with our premium services</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="feature-card bg-gray-50 p-8 text-center">
                    <div class="bus-icon">
                        <i class="fas fa-shield-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Safe Travel</h3>
                    <p class="text-gray-600">GPS tracked buses with trained drivers and strict safety protocols ensure your journey is secure.</p>
                </div>
                
                <div class="feature-card bg-gray-50 p-8 text-center">
                    <div class="bus-icon">
                        <i class="fas fa-rupee-sign text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Affordable Fares</h3>
                    <p class="text-gray-600">Competitive pricing with transparent fares and no hidden charges for all routes.</p>
                </div>
                
                <div class="feature-card bg-gray-50 p-8 text-center">
                    <div class="bus-icon">
                        <i class="fas fa-bus text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">3000+ Buses</h3>
                    <p class="text-gray-600">Largest fleet in Gujarat connecting every corner with frequent departures.</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-8">
                <div class="feature-card bg-gray-50 p-8 text-center">
                    <div class="bus-icon">
                        <i class="fas fa-wifi text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Free WiFi</h3>
                    <p class="text-gray-600">Stay connected on premium buses with complimentary high-speed internet access.</p>
                </div>
                
                <div class="feature-card bg-gray-50 p-8 text-center">
                    <div class="bus-icon">
                        <i class="fas fa-snowflake text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">AC Comfort</h3>
                    <p class="text-gray-600">Climate-controlled environment for a comfortable journey in all weather conditions.</p>
                </div>
                
                <div class="feature-card bg-gray-50 p-8 text-center">
                    <div class="bus-icon">
                        <i class="fas fa-headset text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">24/7 Support</h3>
                    <p class="text-gray-600">Dedicated customer service team available round the clock for assistance.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="text-center mb-14">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">What Our Passengers Say</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Hear from thousands of satisfied travelers across Gujarat</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center text-white font-bold mr-3">RJ</div>
                        <div>
                            <h3 class="font-bold">Rajesh Patel</h3>
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600">"The online booking experience is seamless. Comfortable buses and always on time. Highly recommended for intercity travel in Gujarat."</p>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center text-white font-bold mr-3">SP</div>
                        <div>
                            <h3 class="font-bold">Sunita Mehta</h3>
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600">"The mobile app makes booking so convenient. Clean buses and polite staff. The Ahmedabad to Surat route is my regular commute."</p>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center text-white font-bold mr-3">VP</div>
                        <div>
                            <h3 class="font-bold">Vikram Desai</h3>
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600">"Excellent service at affordable prices. The buses are well-maintained and the drivers are professional. Will definitely travel again with GSRTC."</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Mobile App Section -->
  

    <!-- Footer is included from footer.php -->
    <?php require_once '../includes/footer.php'; ?>
    
    <script>
        // Set default date to today
        const today = new Date();
        const formattedDate = today.toISOString().split('T')[0];
        document.querySelector('input[type="date"]').value = formattedDate;
        document.querySelector('input[type="date"]').min = formattedDate;
        
        // Add animation to route cards on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-pulse');
                }
            });
        }, { threshold: 0.1 });
        
        document.querySelectorAll('.route-card').forEach(card => {
            observer.observe(card);
        });
    </script>
</body>
</html>
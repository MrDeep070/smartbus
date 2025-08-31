<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Fetch bookings with user and bus details
$sql = "SELECT 
            b.id AS booking_id,
            u.name AS user_name,
            bs.bus_no,
            bs.bus_type,
            CONCAT(bs.source, ' → ', bs.destination) AS route,
            bs.date AS journey_date,
            b.seats,
            b.amount_paid,
            b.gst_amount,
            b.discount,
            b.cancellation_fee,
            b.status,
            b.booking_date
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN buses bs ON b.bus_id = bs.id
        ORDER BY b.id DESC";



$stmt = $pdo->query($sql);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include './header.php'; ?>

<div class="container mx-auto px-6 py-6">

    <!-- Flash Messages -->
    <?php if (isset($_GET['success']) && $_GET['success'] === 'deleted'): ?>
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded-lg mb-4 shadow">
            ✅ Booking deleted successfully.
        </div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="bg-red-100 text-red-800 px-4 py-3 rounded-lg mb-4 shadow">
            <?php if ($_GET['error'] === 'notfound') echo "⚠️ Booking not found.";
                  elseif ($_GET['error'] === 'notallowed') echo "🚫 Only cancelled bookings can be deleted."; ?>
        </div>
    <?php endif; ?>

    <h1 class="text-2xl font-bold mb-6 text-gray-800">📑 Manage Bookings</h1>

    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 bg-white rounded-2xl shadow-md overflow-hidden">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-gray-700">
                    <th class="py-3 px-4 text-left font-semibold">Booking ID</th>
                    <th class="py-3 px-4 text-left font-semibold">User</th>
                    <th class="py-3 px-4 text-left font-semibold">Bus</th>
                    <th class="py-3 px-4 text-left font-semibold">Route</th>
                    <th class="py-3 px-4 text-left font-semibold">Date</th>
                    <th class="py-3 px-4 text-left font-semibold">Seats</th>
                    <th class="py-3 px-4 text-left font-semibold">Amount</th>
                    <th class="py-3 px-4 text-left font-semibold">Cancellation Fee</th>
                    <th class="py-3 px-4 text-left font-semibold">Status</th>
                    <th class="py-3 px-4 text-left font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($bookings as $booking): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 px-4"><?= htmlspecialchars($booking['booking_id']) ?></td>
                        <td class="py-3 px-4"><?= htmlspecialchars($booking['user_name']) ?></td>
                        <td class="py-3 px-4 font-medium text-gray-700">
                            <?= htmlspecialchars($booking['bus_no']) ?>
                            <span class="text-sm text-gray-500">(<?= htmlspecialchars($booking['bus_type']) ?>)</span>
                        </td>
                        <td class="py-3 px-4 text-gray-700"><?= htmlspecialchars($booking['route']) ?></td>
                        <td class="py-3 px-4"><?= htmlspecialchars($booking['journey_date']) ?></td>
                        <td class="py-3 px-4"><?= htmlspecialchars($booking['seats']) ?></td>
                        <td class="py-3 px-4 font-semibold text-green-600">₹<?= number_format($booking['amount_paid'], 2) ?></td>
                        <td class="py-3 px-4 text-red-600">₹<?= number_format($booking['cancellation_fee'], 2) ?></td>
                        <td class="py-3 px-4">
                            <?php if ($booking['status'] === 'Cancelled'): ?>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Cancelled</span>
                            <?php elseif ($booking['status'] === 'Confirmed'): ?>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Confirmed</span>
                            <?php else: ?>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700"><?= htmlspecialchars($booking['status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
    <!-- Actions -->
    <a href="view_booking.php?id=<?= $booking['booking_id'] ?>" 
       class="px-2 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded">View</a>

    <?php if (strtolower(trim($booking['status'])) === 'cancelled'): ?>
        <a href="delete_booking.php?id=<?= $booking['booking_id'] ?>" 
           class="px-2 py-1 bg-red-500 hover:bg-red-600 text-white rounded ml-2"
           onclick="return confirm('Are you sure you want to delete this cancelled booking?');">
           Delete
        </a>
    <?php endif; ?>
</td>

                    </tr>
                <?php endforeach; ?>

                <?php if (count($bookings) === 0): ?>
                    <tr>
                        <td colspan="10" class="py-6 px-4 text-center text-gray-500">
                            No bookings found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<?php include '../includes/footer.php'; ?>

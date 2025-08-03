<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/header.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get wallet balance
$stmt = $pdo->prepare("SELECT * FROM wallets WHERE user_id = ?");
$stmt->execute([$user_id]);
$wallet = $stmt->fetch();

// If no wallet record exists, create one
if (!$wallet) {
    $stmt = $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, 0)");
    $stmt->execute([$user_id]);
    $wallet = ['balance' => 0.00];
}

// Get wallet transactions
$stmt = $pdo->prepare("SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY transaction_date DESC");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll();

// Handle add money form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float)$_POST['amount'];
    $account_number = trim($_POST['account_number']);
    $ifsc = trim($_POST['ifsc']);
    $bank_name = trim($_POST['bank_name']);
    $branch = trim($_POST['branch']);

    // Basic validation
    if ($amount <= 0) {
        $error = "Amount must be positive";
    } elseif (strlen($account_number) < 5) {
        $error = "Invalid account number";
    } elseif (strlen($ifsc) < 5) {
        $error = "Invalid IFSC code";
    } else {
        // Add money to wallet
        $new_balance = $wallet['balance'] + $amount;
        $pdo->beginTransaction();

        try {
            // Update wallet
            $stmt = $pdo->prepare("UPDATE wallets SET balance = ? WHERE user_id = ?");
            $stmt->execute([$new_balance, $user_id]);

            // Record transaction
            $stmt = $pdo->prepare("INSERT INTO wallet_transactions 
                                  (user_id, type, amount, description) 
                                  VALUES (?, 'credit', ?, ?)");
            $desc = "Added via Bank Transfer (Ac: $account_number, IFSC: $ifsc)";
            $stmt->execute([$user_id, $amount, $desc]);

            $pdo->commit();
            $_SESSION['success'] = "₹$amount added to your wallet!";
            header("Location: wallet.php");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to add money: " . $e->getMessage();
        }
    }
}
?>

<section class="py-12">
    <div class="container mx-auto px-4 max-w-4xl">
        <h2 class="text-3xl font-bold mb-6">My Wallet</h2>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-bold">Current Balance</h3>
                    <p class="text-3xl font-bold text-green-600">₹<?= number_format($wallet['balance'], 2) ?></p>
                </div>
                <div>
                    <button id="addMoneyBtn" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-lg font-semibold">
                        <i class="fas fa-plus mr-1"></i> Add Money
                    </button>
                </div>
            </div>
            
            <div id="addMoneyForm" class="mt-6 hidden">
                <h3 class="text-lg font-bold mb-4">Add Money via Bank Transfer</h3>
                <form method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 mb-2" for="amount">Amount (₹)</label>
                            <input type="number" step="0.01" min="1" id="amount" name="amount" required 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2" for="account_number">Account Number</label>
                            <input type="text" id="account_number" name="account_number" required 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2" for="ifsc">IFSC Code</label>
                            <input type="text" id="ifsc" name="ifsc" required 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2" for="bank_name">Bank Name</label>
                            <input type="text" id="bank_name" name="bank_name" required 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2" for="branch">Branch</label>
                            <input type="text" id="branch" name="branch" required 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white py-2 px-6 rounded-lg font-semibold">
                        Add Money
                    </button>
                </form>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-xl font-bold mb-4">Transaction History</h3>
            <?php if (empty($transactions)): ?>
                <p class="text-gray-600">No transactions yet.</p>
            <?php else: ?>
                <table class="min-w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-4 text-left">Date</th>
                            <th class="py-2 px-4 text-left">Description</th>
                            <th class="py-2 px-4 text-left">Type</th>
                            <th class="py-2 px-4 text-left">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $txn): ?>
                        <tr class="border-b">
                            <td class="py-3 px-4"><?= date('d M Y, h:i A', strtotime($txn['transaction_date'])) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($txn['description']) ?></td>
                            <td class="py-3 px-4">
                                <span class="<?= $txn['type'] === 'credit' ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= ucfirst($txn['type']) ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 <?= $txn['type'] === 'credit' ? 'text-green-600' : 'text-red-600' ?>">
                                <?= $txn['type'] === 'credit' ? '+' : '-' ?>₹<?= number_format($txn['amount'], 2) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
    document.getElementById('addMoneyBtn').addEventListener('click', function() {
        document.getElementById('addMoneyForm').classList.toggle('hidden');
    });
</script>

<?php require_once '../includes/footer.php'; ?>
<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
// require_once '../includes/header.php';

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
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to add money: " . $e->getMessage();
        }
    }
}
?>
<?php include '../includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wallet | GSRTC</title>
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
        
        .wallet-card {
            background: linear-gradient(135deg, #0c4da2 0%, #0ea5e9 100%);
            border-radius: 16px;
            overflow: hidden;
        }
        
        .transaction-card {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border-radius: 12px;
            overflow: hidden;
        }
        
        .transaction-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
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
        
        .badge {
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .credit-badge {
            background: linear-gradient(45deg, #10b981, #34d399);
        }
        
        .debit-badge {
            background: linear-gradient(45deg, #ef4444, #f87171);
        }
        
        .payment-method {
            transition: all 0.3s ease;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid transparent;
        }
        
        .payment-method:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            border-color: #0ea5e9;
        }
        
        .payment-method.selected {
            border-color: #0c4da2;
            background-color: rgba(12, 77, 162, 0.05);
        }
        
        .animate-pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header is included from header.php -->
    <?php require_once '../includes/header.php'; ?>
    
    <main class="container mx-auto px-4 py-8 max-w-5xl">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">My Wallet</h1>
            <p class="text-gray-600">Manage your funds and view transaction history</p>
        </div>
        
        <!-- Wallet Balance Card -->
        <div class="wallet-card text-white p-6 mb-8 shadow-xl">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold mb-1">Current Balance</h2>
                    <p class="text-4xl font-bold mb-4">₹<?= number_format($wallet['balance'], 2) ?></p>
                    <div class="flex items-center">
                        <i class="fas fa-shield-alt mr-2"></i>
                        <span>Secure Payment Processing</span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="inline-block bg-white/20 p-3 rounded-full mb-4">
                        <i class="fas fa-wallet text-3xl"></i>
                    </div>
                    <p class="text-sm opacity-90">Last updated: <?= date('d M Y, h:i A') ?></p>
                </div>
            </div>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 fade-in">
                <i class="fas fa-exclamation-circle mr-2"></i> <?= $error ?>
            </div>
        <?php endif; ?>
        
        <!-- Add Money Section -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-800">Add Funds to Wallet</h2>
                <button id="addMoneyBtn" class="bg-secondary hover:bg-orange-700 text-white py-2 px-6 rounded-lg font-semibold flex items-center animate-pulse">
                    <i class="fas fa-plus mr-2"></i> Add Money
                </button>
            </div>
            
            <div id="addMoneyForm" class="hidden fade-in">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="payment-method">
                        <div class="p-4">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                    <i class="fas fa-building text-blue-600"></i>
                                </div>
                                <h3 class="font-bold">Bank Transfer</h3>
                            </div>
                            <p class="text-gray-600 text-sm">Add money directly from your bank account</p>
                        </div>
                    </div>
                    
                    <div class="payment-method">
                        <div class="p-4">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                    <i class="fab fa-cc-visa text-blue-600"></i>
                                </div>
                                <h3 class="font-bold">Credit/Debit Card</h3>
                            </div>
                            <p class="text-gray-600 text-sm">Add money using your card (Coming Soon)</p>
                        </div>
                    </div>
                    
                    <div class="payment-method">
                        <div class="p-4">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                    <i class="fas fa-mobile-alt text-blue-600"></i>
                                </div>
                                <h3 class="font-bold">UPI Payment</h3>
                            </div>
                            <p class="text-gray-600 text-sm">Add money via UPI apps (Coming Soon)</p>
                        </div>
                    </div>
                </div>
                
                <h3 class="text-lg font-bold mb-4 text-gray-800 border-b pb-2">Bank Transfer Details</h3>
                <form method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium" for="amount">Amount (₹)</label>
                            <div class="relative">
                                <i class="fas fa-rupee-sign absolute left-3 top-3 text-gray-400"></i>
                                <input type="number" step="0.01" min="1" id="amount" name="amount" required 
                                       class="w-full pl-10 pr-4 py-3 input-field focus:outline-none" 
                                       placeholder="Enter amount">
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium" for="account_number">Account Number</label>
                            <div class="relative">
                                <i class="fas fa-wallet absolute left-3 top-3 text-gray-400"></i>
                                <input type="text" id="account_number" name="account_number" required 
                                       class="w-full pl-10 pr-4 py-3 input-field focus:outline-none" 
                                       placeholder="Account number">
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium" for="ifsc">IFSC Code</label>
                            <div class="relative">
                                <i class="fas fa-code absolute left-3 top-3 text-gray-400"></i>
                                <input type="text" id="ifsc" name="ifsc" required 
                                       class="w-full pl-10 pr-4 py-3 input-field focus:outline-none" 
                                       placeholder="IFSC code">
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium" for="bank_name">Bank Name</label>
                            <div class="relative">
                                <i class="fas fa-university absolute left-3 top-3 text-gray-400"></i>
                                <input type="text" id="bank_name" name="bank_name" required 
                                       class="w-full pl-10 pr-4 py-3 input-field focus:outline-none" 
                                       placeholder="Bank name">
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium" for="branch">Branch</label>
                            <div class="relative">
                                <i class="fas fa-map-marker-alt absolute left-3 top-3 text-gray-400"></i>
                                <input type="text" id="branch" name="branch" required 
                                       class="w-full pl-10 pr-4 py-3 input-field focus:outline-none" 
                                       placeholder="Branch location">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white py-3 px-8 rounded-lg font-semibold flex items-center">
                            <i class="fas fa-check-circle mr-2"></i> Confirm Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Transaction History -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-800">Transaction History</h2>
                <div class="flex space-x-2">
                    <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg">
                        <i class="fas fa-download mr-1"></i> Export
                    </button>
                </div>
            </div>
            
            <?php if (empty($transactions)): ?>
                <div class="text-center py-12">
                    <div class="inline-block bg-gray-100 p-6 rounded-full mb-4">
                        <i class="fas fa-exchange-alt text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-700 mb-2">No transactions yet</h3>
                    <p class="text-gray-600 max-w-md mx-auto">Your transaction history will appear here once you add funds or make payments.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 px-4 text-left text-gray-600 font-medium">Date & Time</th>
                                <th class="py-3 px-4 text-left text-gray-600 font-medium">Description</th>
                                <th class="py-3 px-4 text-left text-gray-600 font-medium">Type</th>
                                <th class="py-3 px-4 text-left text-gray-600 font-medium">Amount</th>
                                <th class="py-3 px-4 text-left text-gray-600 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $txn): ?>
                            <tr class="border-b hover:bg-gray-50 transaction-card">
                                <td class="py-4 px-4">
                                    <div class="text-gray-700 font-medium"><?= date('d M Y', strtotime($txn['transaction_date'])) ?></div>
                                    <div class="text-gray-500 text-sm"><?= date('h:i A', strtotime($txn['transaction_date'])) ?></div>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="font-medium text-gray-800"><?= htmlspecialchars($txn['description']) ?></div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="badge <?= $txn['type'] === 'credit' ? 'credit-badge text-white' : 'debit-badge text-white' ?>">
                                        <?= ucfirst($txn['type']) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-medium <?= $txn['type'] === 'credit' ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= $txn['type'] === 'credit' ? '+' : '-' ?>₹<?= number_format($txn['amount'], 2) ?>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="text-green-600 font-medium">
                                        <i class="fas fa-check-circle mr-1"></i> Completed
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-6 flex justify-between items-center">
                    <div class="text-gray-600">Showing <?= count($transactions) ?> transactions</div>
                    <div class="flex space-x-2">
                        <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="bg-primary text-white py-2 px-4 rounded-lg">1</button>
                        <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg">2</button>
                        <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Security Tips -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-6">
            <div class="flex items-start">
                <div class="mr-4 text-blue-600">
                    <i class="fas fa-shield-alt text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-gray-800 mb-2">Wallet Security Tips</h3>
                    <ul class="list-disc pl-5 text-gray-600 space-y-1">
                        <li>Never share your account details with anyone</li>
                        <li>Use strong passwords and enable two-factor authentication</li>
                        <li>Regularly monitor your transaction history</li>
                        <li>Log out after each session, especially on shared devices</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        // Toggle add money form
        document.getElementById('addMoneyBtn').addEventListener('click', function() {
            const form = document.getElementById('addMoneyForm');
            form.classList.toggle('hidden');
            
            if (!form.classList.contains('hidden')) {
                // Scroll to form
                form.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
        
        // Add animation to payment methods
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                // Remove selected class from all
                document.querySelectorAll('.payment-method').forEach(m => {
                    m.classList.remove('selected');
                });
                
                // Add to clicked
                this.classList.add('selected');
            });
        });
        
        // Set default selected payment method
        document.querySelector('.payment-method').classList.add('selected');
    </script>
    
    <!-- Footer is included from footer.php -->
    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
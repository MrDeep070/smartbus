
<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid email or password!';
    }
}

require_once '../includes/header.php';
?>

<div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow-lg">
    <h2 class="text-3xl font-bold text-center mb-6 text-blue-700">Login to Your Account</h2>
    
    <?php if (isset($_GET['registered'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            Registration successful! Please login.
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-4">
            <label class="block text-gray-700 mb-2" for="email">Email Address</label>
            <input type="email" id="email" name="email" required 
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div class="mb-6">
            <label class="block text-gray-700 mb-2" for="password">Password</label>
            <input type="password" id="password" name="password" required 
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-semibold">
            Login
        </button>
    </form>
    
    <div class="mt-6 text-center">
        <p>Don't have an account? <a href="register.php" class="text-blue-600 hover:underline">Register here</a></p>
        <p class="mt-2"><a href="#" class="text-blue-600 hover:underline">Forgot Password?</a></p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
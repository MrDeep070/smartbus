
<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required!';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format!';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = 'Email already registered!';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$name, $email, $hashed_password])) {
                header('Location: login.php?registered=1');
                exit;
            } else {
                $error = 'Registration failed!';
            }
        }
    }
}

require_once '../includes/header.php';
?>

<div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow-lg">
    <h2 class="text-3xl font-bold text-center mb-6 text-blue-700">Create Account</h2>
    
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-4">
            <label class="block text-gray-700 mb-2" for="name">Full Name</label>
            <input type="text" id="name" name="name" required 
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2" for="email">Email Address</label>
            <input type="email" id="email" name="email" required 
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2" for="password">Password</label>
            <input type="password" id="password" name="password" required 
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div class="mb-6">
            <label class="block text-gray-700 mb-2" for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required 
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-semibold">
            Register
        </button>
    </form>
    
    <div class="mt-6 text-center">
        <p>Already have an account? <a href="login.php" class="text-blue-600 hover:underline">Login here</a></p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
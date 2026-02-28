<?php
session_start();
require_once 'includes/auth.php';

if ($auth->isLoggedIn()) {
    header("Location: " . BASE_PATH . $_SESSION['role'] . "/dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } elseif ($auth->login($username, $password)) {
        header("Location: " . BASE_PATH . $_SESSION['role'] . "/dashboard.php");
        exit();
    } else {
        $error = "Invalid username/email or password";
    }
}

require_once 'includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="bg-gray-800 border border-gray-700 rounded-xl shadow-xl p-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-white">Welcome Back</h2>
                <p class="mt-2 text-sm text-gray-400">
                    Sign in to continue to DanceLive
                </p>
            </div>
            
            <?php if ($error): ?>
                <div class="mt-6 bg-red-900/50 border border-red-700 text-red-200 px-4 py-3 rounded-lg" role="alert">
                    <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <form class="mt-8 space-y-6" method="POST">
                <div class="space-y-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-300 mb-2">Username or Email</label>
                        <input id="username" name="username" type="text" required 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               class="input-field"
                               placeholder="Enter your username or email">
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                        <input id="password" name="password" type="password" required 
                               class="input-field"
                               placeholder="Enter your password">
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full btn-primary">
                        Sign In
                    </button>
                </div>
                
                <div class="text-center text-sm">
                    <span class="text-gray-400">Don't have an account?</span>
                    <a href="<?php echo BASE_PATH; ?>register.php" class="ml-1 text-blue-500 hover:text-blue-400 font-medium">
                        Sign up
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
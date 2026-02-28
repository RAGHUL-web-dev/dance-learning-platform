<?php
session_start();
require_once 'includes/auth.php';

if ($auth->isLoggedIn()) {
    header("Location: " . BASE_PATH . $_SESSION['role'] . "/dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'role' => $_POST['role'] ?? '',
        'full_name' => trim($_POST['full_name'] ?? '')
    ];
    
    // Validation
    $errors = [];
    
    if (empty($data['full_name'])) $errors[] = "Full name is required";
    if (empty($data['username'])) $errors[] = "Username is required";
    elseif (strlen($data['username']) < 3) $errors[] = "Username must be at least 3 characters";
    
    if (empty($data['email'])) $errors[] = "Email is required";
    elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    
    if (empty($data['password'])) $errors[] = "Password is required";
    elseif (strlen($data['password']) < 6) $errors[] = "Password must be at least 6 characters";
    
    if ($data['password'] !== $data['confirm_password']) $errors[] = "Passwords do not match";
    
    if (!in_array($data['role'], ['instructor', 'student'])) $errors[] = "Please select a valid role";
    
    if (empty($errors)) {
        if ($auth->register($data)) {
            header("Location: " . BASE_PATH . $_SESSION['role'] . "/dashboard.php");
            exit();
        } else {
            $error = "Registration failed. Username or email may already exist.";
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

require_once 'includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="bg-gray-800 border border-gray-700 rounded-xl shadow-xl p-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-white">Create Account</h2>
                <p class="mt-2 text-sm text-gray-400">
                    Join DanceLive today
                </p>
            </div>
            
            <?php if ($error): ?>
                <div class="mt-6 bg-red-900/50 border border-red-700 text-red-200 px-4 py-3 rounded-lg" role="alert">
                    <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <form class="mt-8 space-y-6" method="POST" id="registerForm">
                <div class="space-y-4">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-300 mb-2">Full Name</label>
                        <input id="full_name" name="full_name" type="text" required 
                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                               class="input-field"
                               placeholder="John Doe">
                    </div>
                    
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-300 mb-2">Username</label>
                        <input id="username" name="username" type="text" required 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               class="input-field"
                               placeholder="johndoe">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                        <input id="email" name="email" type="email" required 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               class="input-field"
                               placeholder="john@example.com">
                    </div>
                    
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-300 mb-2">I want to join as</label>
                        <select id="role" name="role" required 
                                class="input-field">
                            <option value="" class="bg-gray-700">Select role</option>
                            <option value="student" class="bg-gray-700" <?php echo (isset($_POST['role']) && $_POST['role'] == 'student') ? 'selected' : ''; ?>>Student</option>
                            <option value="instructor" class="bg-gray-700" <?php echo (isset($_POST['role']) && $_POST['role'] == 'instructor') ? 'selected' : ''; ?>>Instructor</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                        <input id="password" name="password" type="password" required 
                               class="input-field"
                               placeholder="••••••••">
                        <div id="password-strength" class="mt-2 h-1 w-full bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full bg-gray-600" style="width: 0%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-300 mb-2">Confirm Password</label>
                        <input id="confirm_password" name="confirm_password" type="password" required 
                               class="input-field"
                               placeholder="••••••••">
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full btn-primary">
                        Create Account
                    </button>
                </div>
                
                <div class="text-center text-sm">
                    <span class="text-gray-400">Already have an account?</span>
                    <a href="<?php echo BASE_PATH; ?>login.php" class="ml-1 text-blue-500 hover:text-blue-400 font-medium">
                        Sign in
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('password').addEventListener('input', function(e) {
    const password = e.target.value;
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]+/)) strength++;
    if (password.match(/[A-Z]+/)) strength++;
    if (password.match(/[0-9]+/)) strength++;
    if (password.match(/[$@#&!]+/)) strength++;
    
    const width = (strength / 5) * 100;
    const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500'];
    const color = strength > 0 ? colors[strength - 1] : 'bg-gray-600';
    
    document.getElementById('password-strength').innerHTML = 
        `<div class="h-full ${color}" style="width: ${width}%"></div>`;
});

document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
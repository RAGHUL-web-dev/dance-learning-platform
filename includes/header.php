<?php
// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database config
require_once __DIR__ . '/../config/database.php';

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dance Learning Platform - Live Interactive Dance Classes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/style.css">
    <style>
        /* Custom dark theme overrides */
        body {
            background-color: #111827;
            color: #e5e7eb;
        }
        .card {
            background-color: #1f2937;
            border: 1px solid #374151;
            border-radius: 0.75rem;
            transition: all 0.2s ease;
        }
        .card:hover {
            border-color: #6b7280;
            transform: translateY(-2px);
        }
        .btn-primary {
            background-color: #3b82f6;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background-color: #2563eb;
        }
        .btn-secondary {
            background-color: #374151;
            color: #e5e7eb;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .btn-secondary:hover {
            background-color: #4b5563;
        }
        .input-field {
            background-color: #374151;
            border: 1px solid #4b5563;
            color: #e5e7eb;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            width: 100%;
            transition: all 0.2s ease;
        }
        .input-field:focus {
            outline: none;
            border-color: #3b82f6;
            ring: 2px solid #3b82f6;
        }
        .input-field::placeholder {
            color: #9ca3af;
        }
        .stat-card {
            background-color: #1f2937;
            border: 1px solid #374151;
            border-radius: 0.75rem;
            padding: 1.5rem;
        }
        .profile-dropdown {
            position: relative;
            display: inline-block;
        }
        .profile-dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #1f2937;
            min-width: 200px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            border: 1px solid #374151;
            border-radius: 0.5rem;
            z-index: 100;
            margin-top: 0.5rem;
        }
        .profile-dropdown:hover .profile-dropdown-content {
            display: block;
        }
        .profile-dropdown-item {
            color: #e5e7eb;
            padding: 0.75rem 1rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: background-color 0.2s;
        }
        .profile-dropdown-item:hover {
            background-color: #374151;
        }
        .profile-dropdown-item:first-child {
            border-radius: 0.5rem 0.5rem 0 0;
        }
        .profile-dropdown-item:last-child {
            border-radius: 0 0 0.5rem 0.5rem;
        }
        .avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            cursor: pointer;
        }
        .nav-link {
            color: #9ca3af;
            transition: color 0.2s;
            font-weight: 500;
        }
        .nav-link:hover {
            color: white;
        }
        .nav-link.active {
            color: #3b82f6;
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-200">
    <nav class="bg-gray-800 border-b border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-8">
                    <a href="<?php echo BASE_PATH; ?>index.php" class="flex items-center space-x-2">
                        <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.871 4.871A10 10 0 0119.6 17.207m-2.829 2.829A10 10 0 013.964 5.964m14.022 14.022L4.414 5.414M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path>
                        </svg>
                        <span class="font-bold text-xl text-white">Dance<span class="text-blue-500">Wave</span></span>
                    </a>
                    
                    <!-- Navigation Links -->
                    <div class="hidden md:flex items-center space-x-4">
                        <a href="<?php echo BASE_PATH; ?>index.php" class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Home</a>
                        <a href="<?php echo BASE_PATH; ?>learning.php" class="nav-link <?php echo $current_page == 'learning.php' ? 'active' : ''; ?>">Learning</a>
                        <?php if (isset($_SESSION['user_id']) && isset($_SESSION['logged_in'])): ?>
                            <a href="<?php echo BASE_PATH; ?><?php echo $_SESSION['role']; ?>/dashboard.php" class="nav-link">Dashboard</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <?php if (isset($_SESSION['user_id']) && isset($_SESSION['logged_in'])): ?>
                        <!-- Profile Dropdown -->
                        <div class="profile-dropdown">
                            <div class="avatar">
                                <?php echo strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)); ?>
                            </div>
                            <div class="profile-dropdown-content">
                                <a href="<?php echo BASE_PATH; ?>profile.php" class="profile-dropdown-item">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Your Profile
                                </a>
                                <a href="<?php echo BASE_PATH; ?>settings.php" class="profile-dropdown-item">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Settings
                                </a>
                                <a href="<?php echo BASE_PATH; ?>subscription.php" class="profile-dropdown-item">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    Subscription
                                </a>
                                <div class="border-t border-gray-700"></div>
                                <a href="<?php echo BASE_PATH; ?>logout.php" class="profile-dropdown-item text-red-400 hover:text-red-300">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo BASE_PATH; ?>login.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition">Login</a>
                        <a href="<?php echo BASE_PATH; ?>register.php" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <main class="min-h-screen">
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/auth.php';
$auth->requireLogin();
$auth->requireRole('instructor');

$user = $auth->getCurrentUser();

// Handle delete single class
if (isset($_POST['delete_class'])) {
    $class_id = $_POST['class_id'] ?? 0;
    
    // Verify the class belongs to this instructor
    $check_sql = "SELECT id FROM dance_classes WHERE id = ? AND instructor_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $class_id, $user['id']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Delete the class
        $delete_sql = "DELETE FROM dance_classes WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $class_id);
        
        if ($delete_stmt->execute()) {
            $_SESSION['success_message'] = "Class deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to delete class.";
        }
    } else {
        $_SESSION['error_message'] = "Class not found or you don't have permission to delete it.";
    }
    
    header("Location: " . BASE_PATH . "instructor/dashboard.php");
    exit();
}

// Handle delete all classes
if (isset($_POST['delete_all_classes'])) {
    // Verify password confirmation for safety
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Verify instructor password
    $check_sql = "SELECT password FROM users WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user['id']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $user_data = $check_result->fetch_assoc();
    
    if (password_verify($confirm_password, $user_data['password'])) {
        // Delete all classes for this instructor
        $delete_sql = "DELETE FROM dance_classes WHERE instructor_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $user['id']);
        
        if ($delete_stmt->execute()) {
            $_SESSION['success_message'] = "All classes deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to delete all classes.";
        }
    } else {
        $_SESSION['error_message'] = "Incorrect password. Classes were not deleted.";
    }
    
    header("Location: " . BASE_PATH . "instructor/dashboard.php");
    exit();
}

// Handle delete completed classes
if (isset($_POST['delete_completed_classes'])) {
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Verify instructor password
    $check_sql = "SELECT password FROM users WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user['id']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $user_data = $check_result->fetch_assoc();
    
    if (password_verify($confirm_password, $user_data['password'])) {
        // Delete completed classes for this instructor
        $delete_sql = "DELETE FROM dance_classes WHERE instructor_id = ? AND status = 'completed'";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $user['id']);
        
        if ($delete_stmt->execute()) {
            $_SESSION['success_message'] = "All completed classes deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to delete completed classes.";
        }
    } else {
        $_SESSION['error_message'] = "Incorrect password. Classes were not deleted.";
    }
    
    header("Location: " . BASE_PATH . "instructor/dashboard.php");
    exit();
}

// Fetch instructor's classes
$sql = "SELECT * FROM dance_classes WHERE instructor_id = ? ORDER BY 
        CASE 
            WHEN status = 'live' THEN 1
            WHEN status = 'upcoming' THEN 2
            ELSE 3
        END, scheduled_date ASC, scheduled_time ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$classes = $stmt->get_result();

// Get class statistics
$stats_sql = "SELECT 
                SUM(CASE WHEN status = 'upcoming' THEN 1 ELSE 0 END) as upcoming,
                SUM(CASE WHEN status = 'live' THEN 1 ELSE 0 END) as live,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                COUNT(*) as total
              FROM dance_classes 
              WHERE instructor_id = ?";
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("i", $user['id']);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

require_once '../includes/header.php';
?>

<style>
/* Delete confirmation modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(4px);
}

.modal-content {
    background-color: #2d2e30;
    margin: 15% auto;
    padding: 2rem;
    border: 1px solid #3c4043;
    border-radius: 1rem;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #3c4043;
}

.modal-header h3 {
    color: white;
    font-size: 1.25rem;
    font-weight: 600;
}

.modal-close {
    color: #9aa0a6;
    cursor: pointer;
    font-size: 1.5rem;
    transition: color 0.2s;
}

.modal-close:hover {
    color: white;
}

.modal-body {
    margin-bottom: 1.5rem;
}

.modal-body p {
    color: #e5e7eb;
    margin-bottom: 1rem;
}

.modal-body .warning {
    background-color: rgba(234, 67, 53, 0.1);
    border: 1px solid rgba(234, 67, 53, 0.3);
    color: #ea4335;
    padding: 0.75rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.modal-body input {
    width: 100%;
    padding: 0.75rem;
    background-color: #3c4043;
    border: 1px solid #5f6368;
    border-radius: 0.5rem;
    color: white;
    margin-top: 0.5rem;
}

.modal-body input:focus {
    outline: none;
    border-color: #8ab4f8;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    border-top: 1px solid #3c4043;
    padding-top: 1.5rem;
}

.delete-options {
    display: flex;
    gap: 1rem;
    margin-left: 1rem;
}

.delete-btn {
    background-color: #d93025;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.delete-btn:hover {
    background-color: #c5221f;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(217, 48, 37, 0.3);
}

.delete-btn.small {
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
}

.action-group {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}
</style>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <!-- Header with Delete Options -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white">Instructor Dashboard</h1>
            <p class="text-gray-400 mt-1">Manage your dance classes</p>
        </div>
        
        <div class="flex items-center space-x-4">
            <!-- Delete Options Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="delete-btn">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete Classes
                </button>
                
                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-64 bg-gray-800 border border-gray-700 rounded-lg shadow-xl z-50">
                    <div class="py-2">
                        <button onclick="showDeleteModal('completed')" class="w-full text-left px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Delete Completed Classes
                        </button>
                        <button onclick="showDeleteModal('all')" class="w-full text-left px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete All Classes
                        </button>
                    </div>
                </div>
            </div>
            
            <a href="<?php echo BASE_PATH; ?>instructor/create-class.php" 
               class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition inline-flex items-center">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create New Class
            </a>
        </div>
    </div>

    <!-- Display messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="mb-6 bg-green-900/50 border border-green-700 text-green-200 px-4 py-3 rounded-lg flex items-center justify-between">
            <span><?php echo $_SESSION['success_message']; ?></span>
            <button onclick="this.parentElement.remove()" class="text-green-200 hover:text-white">✕</button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="mb-6 bg-red-900/50 border border-red-700 text-red-200 px-4 py-3 rounded-lg flex items-center justify-between">
            <span><?php echo $_SESSION['error_message']; ?></span>
            <button onclick="this.parentElement.remove()" class="text-red-200 hover:text-white">✕</button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-5 mb-8">
        <div class="stat-card">
            <p class="text-sm font-medium text-gray-400">Total Classes</p>
            <p class="mt-2 text-3xl font-bold text-white"><?php echo $stats['total'] ?? 0; ?></p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-gray-400">Live Now</p>
            <p class="mt-2 text-3xl font-bold text-green-500"><?php echo $stats['live'] ?? 0; ?></p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-gray-400">Upcoming</p>
            <p class="mt-2 text-3xl font-bold text-yellow-500"><?php echo $stats['upcoming'] ?? 0; ?></p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-gray-400">Completed</p>
            <p class="mt-2 text-3xl font-bold text-blue-500"><?php echo $stats['completed'] ?? 0; ?></p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-gray-400">Cancelled</p>
            <p class="mt-2 text-3xl font-bold text-gray-500"><?php echo $stats['cancelled'] ?? 0; ?></p>
        </div>
    </div>

    <!-- Classes List -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-700 flex justify-between items-center">
            <h2 class="text-lg font-medium text-white">Your Classes</h2>
            <?php if ($classes->num_rows > 0): ?>
                <span class="text-sm text-gray-400"><?php echo $classes->num_rows; ?> classes total</span>
            <?php endif; ?>
        </div>
        
        <?php if ($classes->num_rows === 0): ?>
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-white">No classes yet</h3>
                <p class="mt-2 text-gray-400">Get started by creating your first class.</p>
                <div class="mt-6">
                    <a href="<?php echo BASE_PATH; ?>instructor/create-class.php" class="btn-primary inline-flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create New Class
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="divide-y divide-gray-700">
                <?php while ($class = $classes->fetch_assoc()): ?>
                    <div class="p-6 hover:bg-gray-700/50 transition group">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <h3 class="text-lg font-medium text-white">
                                        <?php echo htmlspecialchars($class['title']); ?>
                                    </h3>
                                    <?php
                                    $statusColors = [
                                        'live' => 'bg-green-900/50 text-green-400 border-green-700',
                                        'upcoming' => 'bg-yellow-900/50 text-yellow-400 border-yellow-700',
                                        'completed' => 'bg-blue-900/50 text-blue-400 border-blue-700',
                                        'cancelled' => 'bg-gray-700 text-gray-300 border-gray-600'
                                    ];
                                    ?>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full border <?php echo $statusColors[$class['status']]; ?>">
                                        <?php echo ucfirst($class['status']); ?>
                                        <?php if ($class['status'] === 'live'): ?>
                                            <span class="ml-1 inline-block h-2 w-2 bg-green-500 rounded-full animate-pulse"></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                
                                <div class="mt-2 flex flex-wrap gap-4 text-sm">
                                    <span class="flex items-center text-gray-400">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <?php echo date('M j, Y', strtotime($class['scheduled_date'])); ?> at <?php echo date('g:i A', strtotime($class['scheduled_time'])); ?>
                                    </span>
                                    <span class="flex items-center text-gray-400">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <?php echo $class['duration']; ?> min
                                    </span>
                                                                        <span class="flex items-center text-gray-400">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <?php echo ucfirst($class['level']); ?> Level
                                    </span>
                                    <span class="flex items-center text-gray-400">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <?php echo ucfirst($class['dance_style']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="action-group">
                                <?php if ($class['status'] === 'upcoming'): ?>
                                    <a href="<?php echo BASE_PATH; ?>instructor/class-room.php?id=<?php echo $class['id']; ?>" 
                                       class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition">
                                        Start Class
                                    </a>
                                <?php elseif ($class['status'] === 'live'): ?>
                                    <a href="<?php echo BASE_PATH; ?>instructor/class-room.php?id=<?php echo $class['id']; ?>" 
                                       class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition animate-pulse">
                                        Join Live
                                    </a>
                                <?php endif; ?>
                                
                                <!-- Delete single class button -->
                                <button onclick="showDeleteSingleModal(<?php echo $class['id']; ?>, '<?php echo addslashes($class['title']); ?>')" 
                                        class="delete-btn small" 
                                        title="Delete this class">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Single Class Modal -->
<div id="deleteSingleModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Delete Class</h3>
            <span class="modal-close" onclick="closeDeleteSingleModal()">&times;</span>
        </div>
        <form method="POST" id="deleteSingleForm">
            <div class="modal-body">
                <p class="warning">
                    <svg class="h-5 w-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    Warning: This action cannot be undone!
                </p>
                <p>Are you sure you want to delete "<span id="deleteClassName" class="font-semibold text-white"></span>"?</p>
                <p class="text-sm text-gray-400 mt-2">This will permanently remove the class and all associated enrollments.</p>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="class_id" id="deleteClassId">
                <input type="hidden" name="delete_class" value="1">
                <button type="button" onclick="closeDeleteSingleModal()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">Cancel</button>
                <button type="submit" class="delete-btn">Delete Class</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete All Classes Modal -->
<div id="deleteAllModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Delete All Classes</h3>
            <span class="modal-close" onclick="closeDeleteAllModal()">&times;</span>
        </div>
        <form method="POST" id="deleteAllForm">
            <div class="modal-body">
                <p class="warning">
                    <svg class="h-5 w-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    ⚠️ DANGER ZONE ⚠️
                </p>
                <p class="font-bold text-white">You are about to delete ALL your classes!</p>
                <p class="text-sm text-gray-300 mt-2">This action:</p>
                <ul class="list-disc list-inside text-sm text-gray-300 mt-2 space-y-1">
                    <li>Cannot be undone</li>
                    <li>Will delete <?php echo $stats['total'] ?? 0; ?> classes permanently</li>
                    <li>Will remove all student enrollments</li>
                    <li>Will cancel all upcoming and live classes</li>
                </ul>
                
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Confirm your password to proceed:</label>
                    <input type="password" name="confirm_password" required 
                           placeholder="Enter your password"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="delete_all_classes" value="1">
                <button type="button" onclick="closeDeleteAllModal()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">Cancel</button>
                <button type="submit" class="delete-btn">Delete All Classes</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Completed Classes Modal -->
<div id="deleteCompletedModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Delete Completed Classes</h3>
            <span class="modal-close" onclick="closeDeleteCompletedModal()">&times;</span>
        </div>
        <form method="POST" id="deleteCompletedForm">
            <div class="modal-body">
                <p class="warning">
                    <svg class="h-5 w-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    Warning: This action cannot be undone!
                </p>
                <p>You are about to delete all <span class="font-semibold text-white"><?php echo $stats['completed'] ?? 0; ?> completed classes</span>.</p>
                <p class="text-sm text-gray-400 mt-2">This will permanently remove these classes and their enrollment records.</p>
                
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Confirm your password to proceed:</label>
                    <input type="password" name="confirm_password" required 
                           placeholder="Enter your password"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="delete_completed_classes" value="1">
                <button type="button" onclick="closeDeleteCompletedModal()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">Cancel</button>
                <button type="submit" class="delete-btn">Delete Completed Classes</button>
            </div>
        </form>
    </div>
</div>

<!-- Include Alpine.js for dropdown -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<script>
// Delete Single Class Modal Functions
function showDeleteSingleModal(classId, className) {
    document.getElementById('deleteClassId').value = classId;
    document.getElementById('deleteClassName').textContent = className;
    document.getElementById('deleteSingleModal').style.display = 'block';
}

function closeDeleteSingleModal() {
    document.getElementById('deleteSingleModal').style.display = 'none';
}

// Delete All Classes Modal Functions
function showDeleteModal(type) {
    if (type === 'all') {
        document.getElementById('deleteAllModal').style.display = 'block';
    } else if (type === 'completed') {
        document.getElementById('deleteCompletedModal').style.display = 'block';
    }
}

function closeDeleteAllModal() {
    document.getElementById('deleteAllModal').style.display = 'none';
}

function closeDeleteCompletedModal() {
    document.getElementById('deleteCompletedModal').style.display = 'none';
}

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// Prevent form resubmission on page refresh
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
</script>

<?php require_once '../includes/footer.php'; ?>
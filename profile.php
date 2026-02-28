<?php
session_start();
require_once 'includes/auth.php';
$auth->requireLogin();

$user = $auth->getCurrentUser();
$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Update basic info
    if (!empty($full_name) && !empty($email)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        } else {
            // Check if email is already taken by another user
            $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("si", $email, $user['id']);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $errors[] = "Email already in use by another account";
            } else {
                $update_sql = "UPDATE users SET full_name = ?, email = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ssi", $full_name, $email, $user['id']);
                
                if ($update_stmt->execute()) {
                    $_SESSION['full_name'] = $full_name;
                    $success = "Profile updated successfully!";
                } else {
                    $errors[] = "Failed to update profile";
                }
            }
        }
    }
    
    // Change password
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        // Verify current password
        $pass_sql = "SELECT password FROM users WHERE id = ?";
        $pass_stmt = $conn->prepare($pass_sql);
        $pass_stmt->bind_param("i", $user['id']);
        $pass_stmt->execute();
        $pass_result = $pass_stmt->get_result();
        $user_data = $pass_result->fetch_assoc();
        
        if (!password_verify($current_password, $user_data['password'])) {
            $errors[] = "Current password is incorrect";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_pass_sql = "UPDATE users SET password = ? WHERE id = ?";
            $update_pass_stmt = $conn->prepare($update_pass_sql);
            $update_pass_stmt->bind_param("si", $hashed_password, $user['id']);
            
            if ($update_pass_stmt->execute()) {
                $success = "Password changed successfully!";
            } else {
                $errors[] = "Failed to change password";
            }
        }
    }
    
    if (!empty($errors)) {
        $error = implode("<br>", $errors);
    }
}

// Get user statistics
if ($user['role'] === 'instructor') {
    // Instructor stats
    $stats_sql = "SELECT 
                    COUNT(*) as total_classes,
                    SUM(CASE WHEN status = 'live' THEN 1 ELSE 0 END) as live_classes,
                    SUM(CASE WHEN status = 'upcoming' THEN 1 ELSE 0 END) as upcoming_classes,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_classes
                  FROM dance_classes 
                  WHERE instructor_id = ?";
    $stats_stmt = $conn->prepare($stats_sql);
    $stats_stmt->bind_param("i", $user['id']);
    $stats_stmt->execute();
    $stats = $stats_stmt->get_result()->fetch_assoc();
    
    // Total students reached
    $students_sql = "SELECT COUNT(DISTINCT student_id) as total_students 
                     FROM class_enrollments e 
                     JOIN dance_classes c ON e.class_id = c.id 
                     WHERE c.instructor_id = ?";
    $students_stmt = $conn->prepare($students_sql);
    $students_stmt->bind_param("i", $user['id']);
    $students_stmt->execute();
    $students_result = $students_stmt->get_result()->fetch_assoc();
    $total_students = $students_result['total_students'] ?? 0;
    
} else {
    // Student stats
    $stats_sql = "SELECT 
                    COUNT(*) as total_enrolled,
                    SUM(CASE WHEN c.status = 'live' THEN 1 ELSE 0 END) as live_classes,
                    SUM(CASE WHEN c.status = 'upcoming' THEN 1 ELSE 0 END) as upcoming_classes,
                    SUM(CASE WHEN c.status = 'completed' THEN 1 ELSE 0 END) as completed_classes
                  FROM class_enrollments e
                  JOIN dance_classes c ON e.class_id = c.id
                  WHERE e.student_id = ?";
    $stats_stmt = $conn->prepare($stats_sql);
    $stats_stmt->bind_param("i", $user['id']);
    $stats_stmt->execute();
    $stats = $stats_stmt->get_result()->fetch_assoc();
    
    // Total classes attended
    $attended_sql = "SELECT COUNT(*) as attended 
                     FROM class_enrollments 
                     WHERE student_id = ? AND status = 'attended'";
    $attended_stmt = $conn->prepare($attended_sql);
    $attended_stmt->bind_param("i", $user['id']);
    $attended_stmt->execute();
    $attended_result = $attended_stmt->get_result()->fetch_assoc();
    $total_attended = $attended_result['attended'] ?? 0;
}

require_once 'includes/header.php';
?>

<style>
.profile-header {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-radius: 1.5rem;
    padding: 3rem;
    margin-bottom: 2rem;
}

.profile-avatar-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3rem;
    font-weight: bold;
    border: 4px solid #3b82f6;
}

.stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 2rem;
}

.stat-box {
    background-color: #1f2937;
    border: 1px solid #374151;
    border-radius: 1rem;
    padding: 1.5rem;
    text-align: center;
}

.stat-value {
    font-size: 2rem;
    font-weight: bold;
    color: white;
    margin-bottom: 0.25rem;
}

.stat-label {
    color: #9ca3af;
    font-size: 0.9rem;
}

.tab-container {
    border-bottom: 1px solid #374151;
    margin-bottom: 2rem;
}

.profile-tab {
    display: inline-block;
    padding: 1rem 2rem;
    color: #9ca3af;
    font-weight: 500;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    transition: all 0.2s;
}

.profile-tab:hover {
    color: white;
}

.profile-tab.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background-color: #1f2937;
    border: 1px solid #374151;
    border-radius: 0.75rem;
    margin-bottom: 0.75rem;
    transition: all 0.2s;
}

.activity-item:hover {
    border-color: #4b5563;
    transform: translateX(4px);
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #374151;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3b82f6;
}
</style>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="flex flex-col md:flex-row items-center gap-8">
            <div class="profile-avatar-large">
                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
            </div>
            <div class="flex-1 text-center md:text-left">
                <h1 class="text-3xl font-bold text-white mb-2"><?php echo htmlspecialchars($user['full_name']); ?></h1>
                <p class="text-xl text-gray-400 mb-4">@<?php echo htmlspecialchars($user['username']); ?></p>
                <div class="flex items-center justify-center md:justify-start gap-4">
                    <span class="px-3 py-1 bg-blue-600/20 text-blue-500 rounded-full text-sm font-medium">
                        <?php echo ucfirst($user['role']); ?>
                    </span>
                    <span class="text-gray-400">Joined <?php echo date('F Y', strtotime($user['created_at'] ?? 'now')); ?></span>
                </div>
            </div>
            <div class="flex gap-3">
                <button class="btn-secondary" onclick="document.getElementById('editProfileBtn').click()">
                    <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                    </svg>
                    Edit Profile
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stat-grid">
        <?php if ($user['role'] === 'instructor'): ?>
            <div class="stat-box">
                <div class="stat-value"><?php echo $stats['total_classes'] ?? 0; ?></div>
                <div class="stat-label">Total Classes</div>
            </div>
            <div class="stat-box">
                <div class="stat-value text-green-500"><?php echo $stats['live_classes'] ?? 0; ?></div>
                <div class="stat-label">Live Now</div>
            </div>
            <div class="stat-box">
                <div class="stat-value text-yellow-500"><?php echo $stats['upcoming_classes'] ?? 0; ?></div>
                <div class="stat-label">Upcoming</div>
            </div>
            <div class="stat-box">
                <div class="stat-value text-blue-500"><?php echo $total_students ?? 0; ?></div>
                <div class="stat-label">Students Reached</div>
            </div>
        <?php else: ?>
            <div class="stat-box">
                <div class="stat-value"><?php echo $stats['total_enrolled'] ?? 0; ?></div>
                <div class="stat-label">Classes Enrolled</div>
            </div>
            <div class="stat-box">
                <div class="stat-value text-green-500"><?php echo $stats['live_classes'] ?? 0; ?></div>
                <div class="stat-label">Live Classes</div>
            </div>
            <div class="stat-box">
                <div class="stat-value text-yellow-500"><?php echo $stats['upcoming_classes'] ?? 0; ?></div>
                <div class="stat-label">Upcoming</div>
            </div>
            <div class="stat-box">
                <div class="stat-value text-blue-500"><?php echo $total_attended ?? 0; ?></div>
                <div class="stat-label">Classes Attended</div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Display messages -->
    <?php if ($error): ?>
        <div class="mt-6 bg-red-900/50 border border-red-700 text-red-200 px-4 py-3 rounded-lg">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="mt-6 bg-green-900/50 border border-green-700 text-green-200 px-4 py-3 rounded-lg">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="tab-container mt-8">
        <button class="profile-tab active" onclick="switchTab('profile')">Profile Information</button>
        <button class="profile-tab" onclick="switchTab('activity')">Recent Activity</button>
        <button class="profile-tab" onclick="switchTab('settings')">Account Settings</button>
    </div>

    <!-- Profile Information Tab -->
    <div id="profile-tab" class="tab-content active">
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
            <h3 class="text-xl font-semibold text-white mb-6">Personal Information</h3>
            
            <form method="POST" class="space-y-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Full Name</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" 
                               class="input-field">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Username</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" 
                               class="input-field" readonly disabled>
                        <p class="text-xs text-gray-500 mt-1">Username cannot be changed</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Email Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                               class="input-field">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Role</label>
                        <input type="text" value="<?php echo ucfirst($user['role']); ?>" 
                               class="input-field" readonly disabled>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="btn-primary" id="editProfileBtn">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Recent Activity Tab -->
    <div id="activity-tab" class="tab-content">
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
            <h3 class="text-xl font-semibold text-white mb-6">Recent Activity</h3>
            
            <div class="space-y-3">
                <!-- Sample activities - in production, fetch from database -->
                <div class="activity-item">
                    <div class="activity-icon">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-white">Joined class "Ballet Basics"</p>
                        <p class="text-sm text-gray-400">2 hours ago</p>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-white">Completed "Hip Hop Fundamentals"</p>
                        <p class="text-sm text-gray-400">Yesterday</p>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-white">Left feedback for "Contemporary Dance"</p>
                        <p class="text-sm text-gray-400">3 days ago</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Settings Tab -->
    <div id="settings-tab" class="tab-content">
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
            <h3 class="text-xl font-semibold text-white mb-6">Change Password</h3>
            
            <form method="POST" class="space-y-6 max-w-md">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Current Password</label>
                    <input type="password" name="current_password" class="input-field">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">New Password</label>
                    <input type="password" name="new_password" class="input-field">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="input-field">
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="btn-primary">
                        Update Password
                    </button>
                </div>
            </form>
            
            <div class="mt-8 pt-8 border-t border-gray-700">
                <h4 class="text-lg font-medium text-white mb-4">Danger Zone</h4>
                <button class="delete-btn" onclick="if(confirm('Are you sure you want to deactivate your account? This action cannot be undone.')) { alert('Account deactivation requested'); }">
                    Deactivate Account
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.profile-tab').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Add active class to clicked button
    event.target.classList.add('active');
}
</script>

<?php require_once 'includes/footer.php'; ?>
<?php
// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/auth.php';
$auth->requireLogin();
$auth->requireRole('instructor');

$user = $auth->getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $dance_style = $_POST['dance_style'] ?? '';
    $level = $_POST['level'] ?? 'beginner';
    $scheduled_date = $_POST['scheduled_date'] ?? '';
    $scheduled_time = $_POST['scheduled_time'] ?? '';
    $duration = $_POST['duration'] ?? 60;
    
    // Validate inputs
    $errors = [];
    if (empty($title)) $errors[] = "Title is required";
    if (empty($scheduled_date)) $errors[] = "Date is required";
    if (empty($scheduled_time)) $errors[] = "Time is required";
    
    if (empty($errors)) {
        // Generate unique meeting ID
        $meeting_id = 'dance_' . uniqid() . '_' . bin2hex(random_bytes(4));
        
        $sql = "INSERT INTO dance_classes (instructor_id, title, description, dance_style, level, scheduled_date, scheduled_time, duration, meeting_link) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssssis", 
            $user['id'], 
            $title, 
            $description, 
            $dance_style, 
            $level, 
            $scheduled_date, 
            $scheduled_time, 
            $duration, 
            $meeting_id
        );
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Class created successfully!";
            header("Location: " . BASE_PATH . "instructor/dashboard.php");
            exit();
        } else {
            $error = "Failed to create class. Please try again.";
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

require_once '../includes/header.php';
?>

<div class="max-w-3xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Create New Dance Class
            </h2>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo $error; ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" class="mt-8 space-y-6 bg-white shadow sm:rounded-lg p-6">
        <div class="grid grid-cols-1 gap-6">
            <div>
                <label for="title" class="block text-sm font-medium text-black">Class Title</label>
                <input type="text" name="title" id="title" required 
                       class="text-black mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-pink-500 focus:border-pink-500 sm:text-sm">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-black">Description</label>
                <textarea name="description" id="description" rows="4" 
                          class="text-black mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-pink-500 focus:border-pink-500 sm:text-sm"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="dance_style" class="block text-sm font-medium text-black">Dance Style</label>
                    <select name="dance_style" id="dance_style" required 
                            class="text-black mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-pink-500 focus:border-pink-500 sm:text-sm rounded-md">
                        <option value="">Select style</option>
                        <option value="ballet">Ballet</option>
                        <option value="hiphop">Hip Hop</option>
                        <option value="contemporary">Contemporary</option>
                        <option value="jazz">Jazz</option>
                        <option value="tap">Tap</option>
                        <option value="ballroom">Ballroom</option>
                        <option value="salsa">Salsa</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label for="level" class="block text-sm font-medium text-black">Level</label>
                    <select name="level" id="level" required 
                            class="text-black mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-pink-500 focus:border-pink-500 sm:text-sm rounded-md">
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="scheduled_date" class="block text-sm font-medium text-black">Date</label>
                    <input type="date" name="scheduled_date" id="scheduled_date" required 
                           min="<?php echo date('Y-m-d'); ?>"
                           class="text-black mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-pink-500 focus:border-pink-500 sm:text-sm">
                </div>

                <div>
                    <label for="scheduled_time" class="block text-sm font-medium text-black">Time</label>
                    <input type="time" name="scheduled_time" id="scheduled_time" required 
                           class="text-black mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-pink-500 focus:border-pink-500 sm:text-sm">
                </div>

                <div>
                    <label for="duration" class="block text-sm font-medium text-black">Duration (minutes)</label>
                    <input type="number" name="duration" id="duration" value="60" min="15" max="180" step="15" required 
                           class="text-black `mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-pink-500 focus:border-pink-500 sm:text-sm">
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="<?php echo BASE_PATH; ?>instructor/dashboard.php" 
               class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-black hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                Cancel
            </a>
            <button type="submit" 
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                Create Class
            </button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
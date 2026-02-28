<?php
// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/auth.php';
$auth->requireLogin();
$auth->requireRole('student');

$user = $auth->getCurrentUser();

// Fetch available classes (excluding completed and classes student already enrolled in)
$sql = "SELECT c.*, 
        u.full_name as instructor_name,
        CASE WHEN e.id IS NOT NULL THEN 1 ELSE 0 END as enrolled
        FROM dance_classes c 
        JOIN users u ON c.instructor_id = u.id
        LEFT JOIN class_enrollments e ON c.id = e.class_id AND e.student_id = ?
        WHERE c.status IN ('upcoming', 'live')
        AND c.scheduled_date >= CURDATE()
        ORDER BY 
            CASE WHEN c.status = 'live' THEN 0 ELSE 1 END,
            c.scheduled_date ASC, 
            c.scheduled_time ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$classes = $stmt->get_result();

// Fetch enrolled classes
$enrolled_sql = "SELECT c.*, u.full_name as instructor_name 
                 FROM class_enrollments e
                 JOIN dance_classes c ON e.class_id = c.id
                 JOIN users u ON c.instructor_id = u.id
                 WHERE e.student_id = ? 
                 AND c.status IN ('upcoming', 'live')
                 ORDER BY c.scheduled_date ASC, c.scheduled_time ASC";

$enrolled_stmt = $conn->prepare($enrolled_sql);
$enrolled_stmt->bind_param("i", $user['id']);
$enrolled_stmt->execute();
$enrolled_classes = $enrolled_stmt->get_result();

require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Student Dashboard
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Find and join live dance classes
            </p>
        </div>
    </div>

    <!-- My Enrolled Classes -->
    <?php if ($enrolled_classes->num_rows > 0): ?>
    <div class="mt-8">
        <h3 class="text-lg leading-6 font-medium text-gray-900">My Upcoming Classes</h3>
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <?php while ($class = $enrolled_classes->fetch_assoc()): ?>
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center justify-between">
                            <h4 class="text-lg font-medium text-gray-900 truncate">
                                <?php echo htmlspecialchars($class['title']); ?>
                            </h4>
                            <?php if ($class['status'] === 'live'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <span class="h-2 w-2 bg-green-500 rounded-full mr-1 animate-pulse"></span>
                                    LIVE
                                </span>
                            <?php endif; ?>
                        </div>
                        <p class="mt-1 text-sm text-gray-600">
                            Instructor: <?php echo htmlspecialchars($class['instructor_name']); ?>
                        </p>
                        <p class="mt-2 text-sm text-gray-500">
                            <?php echo date('F j, Y', strtotime($class['scheduled_date'])); ?><br>
                            <?php echo date('g:i A', strtotime($class['scheduled_time'])); ?> (<?php echo $class['duration']; ?> min)
                        </p>
                        <?php if ($class['status'] === 'live'): ?>
                            <div class="mt-4">
                                <a href="<?php echo BASE_URL; ?>/student/class-room.php?meeting=<?php echo urlencode($class['meeting_link']); ?>" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                    Join Class Now
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Available Classes -->
    <div class="mt-8">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Available Classes</h3>
        
        <?php if ($classes->num_rows === 0): ?>
            <div class="mt-4 text-center py-12 bg-white rounded-lg shadow">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No classes available</h3>
                <p class="mt-1 text-sm text-gray-500">Check back later for new classes.</p>
            </div>
        <?php else: ?>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <?php while ($class = $classes->fetch_assoc()): ?>
                    <div class="bg-white overflow-hidden shadow rounded-lg <?php echo $class['status'] === 'live' ? 'ring-2 ring-green-500' : ''; ?>">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center justify-between">
                                <h4 class="text-lg font-medium text-gray-900 truncate">
                                    <?php echo htmlspecialchars($class['title']); ?>
                                </h4>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    <?php echo $class['status'] === 'live' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                    <?php if ($class['status'] === 'live'): ?>
                                        <span class="h-2 w-2 bg-green-500 rounded-full mr-1 animate-pulse"></span>
                                    <?php endif; ?>
                                    <?php echo ucfirst($class['status']); ?>
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-600">
                                Instructor: <?php echo htmlspecialchars($class['instructor_name']); ?>
                            </p>
                            <p class="mt-2 text-sm text-gray-500">
                                Style: <?php echo ucfirst($class['dance_style']); ?> | Level: <?php echo ucfirst($class['level']); ?><br>
                                <?php echo date('F j, Y', strtotime($class['scheduled_date'])); ?><br>
                                <?php echo date('g:i A', strtotime($class['scheduled_time'])); ?>
                            </p>
                            <?php if (!$class['enrolled'] && $class['status'] !== 'live'): ?>
                                <div class="mt-4">
                                    <button onclick="enrollInClass(<?php echo $class['id']; ?>)" 
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-pink-600 hover:bg-pink-700">
                                        Enroll Now
                                    </button>
                                </div>
                            <?php elseif ($class['status'] === 'live'): ?>
                                <div class="mt-4">
                                    <a href="<?php echo BASE_URL; ?>/student/class-room.php?meeting=<?php echo urlencode($class['meeting_link']); ?>" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                        Join Live Class
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function enrollInClass(classId) {
    fetch('<?php echo BASE_URL; ?>/api/enroll-class.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ class_id: classId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to enroll: ' + data.message);
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/auth.php';
$auth->requireLogin();
$auth->requireRole('instructor');

$class_id = $_GET['id'] ?? 0;

if (!$class_id) {
    header("Location: " . BASE_PATH . "instructor/dashboard.php");
    exit();
}

// Fetch class details
$sql = "SELECT * FROM dance_classes WHERE id = ? AND instructor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $class_id, $_SESSION['user_id']);
$stmt->execute();
$class = $stmt->get_result()->fetch_assoc();

if (!$class) {
    header("Location: " . BASE_PATH . "instructor/dashboard.php");
    exit();
}

// Update class status to live if it's upcoming
if ($class['status'] === 'upcoming') {
    $update_sql = "UPDATE dance_classes SET status = 'live' WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $class_id);
    $update_stmt->execute();
    $class['status'] = 'live';
}

// Get enrolled students count
$student_sql = "SELECT COUNT(*) as count FROM class_enrollments WHERE class_id = ?";
$student_stmt = $conn->prepare($student_sql);
$student_stmt->bind_param("i", $class_id);
$student_stmt->execute();
$student_count = $student_stmt->get_result()->fetch_assoc()['count'];

require_once '../includes/header.php';
?>

<style>
    /* Google Meet Style Layout */
    .meet-container {
        height: calc(100vh - 4rem);
        display: flex;
        flex-direction: column;
        background-color: #202124;
    }
    
    .video-grid {
        flex: 1;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 8px;
        padding: 8px;
        align-content: center;
        background-color: #202124;
    }
    
    .video-tile {
        position: relative;
        background-color: #3c4043;
        border-radius: 8px;
        overflow: hidden;
        aspect-ratio: 16/9;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .video-tile:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        z-index: 10;
    }
    
    .video-tile video,
    .video-tile canvas {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .video-label {
        position: absolute;
        bottom: 12px;
        left: 12px;
        background-color: rgba(0, 0, 0, 0.6);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
        backdrop-filter: blur(4px);
    }
    
    .video-status {
        position: absolute;
        top: 12px;
        right: 12px;
        background-color: rgba(0, 0, 0, 0.6);
        color: #34a853;
        padding: 4px 8px;
        border-radius: 20px;
        font-size: 0.75rem;
        display: flex;
        align-items: center;
        gap: 4px;
        backdrop-filter: blur(4px);
    }
    
    .video-status.live {
        color: #ea4335;
    }
    
    .meet-controls {
        background-color: #2d2e30;
        border-top: 1px solid #3c4043;
        padding: 16px;
        display: flex;
        justify-content: center;
        gap: 12px;
        position: sticky;
        bottom: 0;
    }
    
    .control-btn {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background-color: #3c4043;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        border: none;
    }
    
    .control-btn:hover {
        background-color: #4a4d51;
    }
    
    .control-btn.danger {
        background-color: #d93025;
    }
    
    .control-btn.danger:hover {
        background-color: #c5221f;
    }
    
    .control-btn.muted {
        background-color: #ea4335;
    }
    
    .meet-sidebar {
        position: fixed;
        right: 0;
        top: 4rem;
        width: 280px;
        height: calc(100vh - 4rem);
        background-color: #2d2e30;
        border-left: 1px solid #3c4043;
        padding: 20px;
        overflow-y: auto;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        z-index: 50;
    }
    
    .meet-sidebar.open {
        transform: translateX(0);
    }
    
    .sidebar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        color: white;
    }
    
    .sidebar-header h3 {
        font-size: 1.1rem;
        font-weight: 500;
    }
    
    .close-sidebar {
        cursor: pointer;
        padding: 4px;
    }
    
    .participant-item {
        display: flex;
        align-items: center;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 4px;
        color: white;
    }
    
    .participant-item:hover {
        background-color: #3c4043;
    }
    
    .participant-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: #8ab4f8;
        color: #202124;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
        margin-right: 12px;
    }
    
    .participant-info {
        flex: 1;
    }
    
    .participant-name {
        font-size: 0.95rem;
    }
    
    .participant-status {
        font-size: 0.75rem;
        color: #9aa0a6;
    }
    
    .participant-status.online {
        color: #34a853;
    }
    
    .meeting-info {
        position: fixed;
        top: 5rem;
        left: 20px;
        background-color: #2d2e30;
        border-radius: 30px;
        padding: 8px 16px;
        color: white;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        z-index: 40;
    }
    
    .meeting-info .live-badge {
        background-color: #d93025;
        padding: 4px 8px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .video-grid {
            grid-template-columns: 1fr;
        }
        
        .meeting-info {
            top: 4.5rem;
            left: 10px;
            right: 10px;
            width: auto;
            justify-content: center;
        }
    }
    
    /* Fullscreen mode */
    .video-tile.fullscreen {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 1000;
        border-radius: 0;
        aspect-ratio: auto;
    }
    
    .fullscreen .video-label {
        font-size: 1rem;
        bottom: 20px;
        left: 20px;
    }
</style>

<div class="meet-container">
    <!-- Meeting Info Bar -->
    <div class="meeting-info">
        <span class="live-badge">LIVE</span>
        <span><?php echo htmlspecialchars($class['title']); ?></span>
        <span class="text-gray-400">•</span>
        <span><?php echo $student_count; ?> students enrolled</span>
    </div>

    <!-- Video Grid -->
    <div class="video-grid" id="video-container">
        <!-- Local video (instructor) -->
        <div class="video-tile" id="local-tile">
            <video id="localVideo" autoplay playsinline muted></video>
            <div class="video-label">
                <span class="flex items-center">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    You (Instructor)
                </span>
            </div>
            <div class="video-status" id="local-status">
                <span class="inline-block h-2 w-2 bg-green-500 rounded-full animate-pulse mr-1"></span>
                Live
            </div>
        </div>
        <!-- Student videos will be added here dynamically -->
    </div>

    <!-- Meeting Controls -->
    <div class="meet-controls">
        <button class="control-btn" id="muteAudio" title="Mute microphone">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
            </svg>
        </button>
        
        <button class="control-btn" id="muteVideo" title="Turn off camera">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
        </button>
        
        <button class="control-btn" id="shareScreen" title="Share screen">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
        </button>
        
        <button class="control-btn" id="toggleSidebar" title="Show participants">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
        </button>
        
        <button class="control-btn danger" id="endCall" title="End class">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.517l2.257-1.128a1 1 0 00.502-1.21L9.228 3.684A1 1 0 008.28 3H5z"></path>
            </svg>
        </button>
    </div>

    <!-- Sidebar -->
    <div class="meet-sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>Participants (<span id="participant-count">0</span>)</h3>
            <svg class="close-sidebar h-6 w-6 text-gray-400 hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </div>
        
        <!-- Participants list -->
        <div id="participants-list">
            <div class="participant-item">
                <div class="participant-avatar">Y</div>
                <div class="participant-info">
                    <div class="participant-name">You (Instructor)</div>
                    <div class="participant-status online">● Online</div>
                </div>
            </div>
            <!-- Other participants will be added here -->
        </div>
        
        <!-- Share meeting link -->
        <div class="mt-6 pt-6 border-t border-gray-700">
            <h4 class="text-white text-sm font-medium mb-3">Meeting Link</h4>
            <div class="flex">
                <input type="text" id="meetingLink" value="<?php echo BASE_URL; ?>/student/class-room.php?meeting=<?php echo $class['meeting_link']; ?>" readonly 
                       class="flex-1 bg-gray-700 text-white text-sm px-3 py-2 rounded-l-lg border-0 focus:ring-1 focus:ring-blue-500">
                <button onclick="copyMeetingLink()" 
                        class="bg-blue-600 text-white px-4 py-2 rounded-r-lg text-sm hover:bg-blue-700 transition">
                    Copy
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle sidebar
document.getElementById('toggleSidebar').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('open');
});

document.querySelector('.close-sidebar').addEventListener('click', function() {
    document.getElementById('sidebar').classList.remove('open');
});

// Copy meeting link
function copyMeetingLink() {
    var copyText = document.getElementById("meetingLink");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
    
    // Show temporary tooltip
    const btn = event.target;
    const originalText = btn.textContent;
    btn.textContent = 'Copied!';
    setTimeout(() => {
        btn.textContent = originalText;
    }, 2000);
}

// Fullscreen toggle for video tiles
document.addEventListener('click', function(e) {
    const tile = e.target.closest('.video-tile');
    if (tile && !e.target.closest('.control-btn')) {
        tile.classList.toggle('fullscreen');
    }
});

// Update participant count
function updateParticipantCount(count) {
    document.getElementById('participant-count').textContent = count;
}

// Initialize WebRTC
document.addEventListener('DOMContentLoaded', function() {
    if (typeof initializeWebRTC === 'function') {
        initializeWebRTC(
            'instructor', 
            '<?php echo $class_id; ?>', 
            <?php echo $_SESSION['user_id']; ?>, 
            '<?php echo addslashes($_SESSION['full_name']); ?>'
        );
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
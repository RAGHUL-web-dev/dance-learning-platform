<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'join':
        handleJoin($conn, $_SESSION['user_id'], $_SESSION['role']);
        break;
    case 'leave':
        handleLeave($conn, $_SESSION['user_id']);
        break;
    case 'send_message':
        handleSendMessage($conn, $_SESSION['user_id'], $_SESSION['role']);
        break;
    case 'get_messages':
        handleGetMessages($conn, $_SESSION['user_id']);
        break;
    case 'get_participants':
        handleGetParticipants($conn, $_SESSION['user_id']);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function handleJoin($conn, $userId, $userRole) {
    $room_id = $_POST['room_id'] ?? '';
    
    if (empty($room_id)) {
        echo json_encode(['success' => false, 'message' => 'Room ID required']);
        return;
    }
    
    // Add to active users
    $sql = "INSERT INTO active_users (room_id, user_id, user_role) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE last_seen = NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sis", $room_id, $userId, $userRole);
    $stmt->execute();
    
    // Get user name
    $name_sql = "SELECT full_name FROM users WHERE id = ?";
    $name_stmt = $conn->prepare($name_sql);
    $name_stmt->bind_param("i", $userId);
    $name_stmt->execute();
    $name_result = $name_stmt->get_result();
    $user_name = $name_result->fetch_assoc()['full_name'];
    
    // Send join message to others in the room
    $message = json_encode(['userId' => $userId, 'role' => $userRole, 'name' => $user_name]);
    $sql = "INSERT INTO signaling (room_id, user_id, user_role, message_type, message) 
            VALUES (?, ?, ?, 'join', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siss", $room_id, $userId, $userRole, $message);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
}

function handleLeave($conn, $userId) {
    // Get user's room before removing
    $sql = "SELECT room_id, user_role FROM active_users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        // Send leave message
        $message = json_encode(['userId' => $userId]);
        $sql = "INSERT INTO signaling (room_id, user_id, user_role, message_type, message) 
                VALUES (?, ?, ?, 'leave', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siss", $user['room_id'], $userId, $user['user_role'], $message);
        $stmt->execute();
        
        // Remove from active users
        $sql = "DELETE FROM active_users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
    }
    
    echo json_encode(['success' => true]);
}

function handleSendMessage($conn, $userId, $userRole) {
    $room_id = $_POST['room_id'] ?? '';
    $message_type = $_POST['message_type'] ?? '';
    $message = $_POST['message'] ?? '';
    $target_user_id = $_POST['target_user_id'] ?? null;
    
    if (empty($room_id) || empty($message_type) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }
    
    $sql = "INSERT INTO signaling (room_id, user_id, user_role, message_type, message, target_user_id) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisssi", $room_id, $userId, $userRole, $message_type, $message, $target_user_id);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message_id' => $stmt->insert_id]);
}

function handleGetMessages($conn, $userId) {
    // Get messages for this user (broadcast or targeted to them)
    $sql = "SELECT s.*, u.full_name as user_name 
            FROM signaling s
            LEFT JOIN users u ON s.user_id = u.id
            WHERE s.room_id IN (
                SELECT room_id FROM active_users WHERE user_id = ?
            )
            AND (s.target_user_id IS NULL OR s.target_user_id = ?)
            AND s.created_at > DATE_SUB(NOW(), INTERVAL 30 SECOND)
            ORDER BY s.created_at ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            'id' => $row['id'],
            'user_id' => $row['user_id'],
            'user_name' => $row['user_name'],
            'user_role' => $row['user_role'],
            'message_type' => $row['message_type'],
            'message' => json_decode($row['message'], true),
            'target_user_id' => $row['target_user_id'],
            'created_at' => $row['created_at']
        ];
    }
    
    // Delete old messages
    $delete_sql = "DELETE FROM signaling WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 SECOND)";
    $conn->query($delete_sql);
    
    echo json_encode(['success' => true, 'messages' => $messages]);
}

function handleGetParticipants($conn, $userId) {
    $sql = "SELECT au.user_id, au.user_role, au.last_seen, u.full_name 
            FROM active_users au
            JOIN users u ON au.user_id = u.id
            WHERE au.room_id IN (
                SELECT room_id FROM active_users WHERE user_id = ?
            )
            AND au.user_id != ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $participants = [];
    while ($row = $result->fetch_assoc()) {
        $participants[] = [
            'user_id' => $row['user_id'],
            'user_role' => $row['user_role'],
            'full_name' => $row['full_name'],
            'last_seen' => $row['last_seen']
        ];
    }
    
    echo json_encode(['success' => true, 'participants' => $participants]);
}
?>
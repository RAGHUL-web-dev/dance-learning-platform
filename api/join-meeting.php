<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!$auth->isLoggedIn() || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$class_id = $data['class_id'] ?? 0;

// Check if class exists and is live
$sql = "SELECT id FROM dance_classes WHERE id = ? AND status = 'live'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Class is not live']);
    exit();
}

// Enroll student if not already enrolled
$enroll_sql = "INSERT IGNORE INTO class_enrollments (class_id, student_id) VALUES (?, ?)";
$enroll_stmt = $conn->prepare($enroll_sql);
$enroll_stmt->bind_param("ii", $class_id, $_SESSION['user_id']);

if ($enroll_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Joined class successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to join class']);
}
?>
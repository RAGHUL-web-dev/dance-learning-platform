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

if (!$class_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid class ID']);
    exit();
}

// Check if class exists and is upcoming
$sql = "SELECT id, status FROM dance_classes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();
$class = $result->fetch_assoc();

if (!$class) {
    echo json_encode(['success' => false, 'message' => 'Class not found']);
    exit();
}

if ($class['status'] !== 'upcoming') {
    echo json_encode(['success' => false, 'message' => 'Class is not available for enrollment']);
    exit();
}

// Check if already enrolled
$check_sql = "SELECT id FROM class_enrollments WHERE class_id = ? AND student_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $class_id, $_SESSION['user_id']);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Already enrolled in this class']);
    exit();
}

// Enroll student
$enroll_sql = "INSERT INTO class_enrollments (class_id, student_id) VALUES (?, ?)";
$enroll_stmt = $conn->prepare($enroll_sql);
$enroll_stmt->bind_param("ii", $class_id, $_SESSION['user_id']);

if ($enroll_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Enrolled successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to enroll']);
}
?>
<?php
session_start();
require_once '../includes/db-conn.php';

header('Content-Type: application/json');

// Determine user role & ID from session
$user_id = null;
$user_name = null;
$user_role = null;

if (isset($_SESSION['sadmin_id'])) {
    $user_id = $_SESSION['sadmin_id'];
    $user_role = 'sadmin';
    $sql = "SELECT name FROM sadmins WHERE id = ?";
} elseif (isset($_SESSION['lecturer_id'])) {
    $user_id = $_SESSION['lecturer_id'];
    $user_role = 'lecturer';
    $sql = "SELECT name FROM lecturers WHERE id = ?";
} elseif (isset($_SESSION['student_id'])) {
    $user_id = $_SESSION['student_id'];
    $user_role = 'student';
    $sql = "SELECT name FROM students WHERE id = ?";
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get user name
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

$user_name = $user['name'];

$meeting_id = isset($_POST['meeting_id']) ? intval($_POST['meeting_id']) : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if ($meeting_id <= 0 || $message === '') {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

// Insert chat message
$stmt = $conn->prepare("INSERT INTO meeting_chat (meeting_id, user_id, user_name, user_role, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit();
}
$stmt->bind_param("iisss", $meeting_id, $user_id, $user_name, $user_role, $message);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Insert failed: ' . $stmt->error]);
}
$stmt->close();
$conn->close();

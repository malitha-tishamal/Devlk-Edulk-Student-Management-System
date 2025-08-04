<?php
session_start();
require_once '../includes/db-conn.php';

header('Content-Type: application/json');
if (!isset($_SESSION['sadmin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$subject_id = intval($_POST['subject_id'] ?? 0);
$lecture_type = $_POST['lecture_type'] ?? 'Zoom';
$access_level = $_POST['access_level'] ?? 'public';
$view_limit = intval($_POST['view_limit_minutes'] ?? 0);

if (!$id || !$title || !$subject_id) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$stmt = $conn->prepare("UPDATE recordings SET title=?, description=?, subject_id=?, lecture_type=?, access_level=?, view_limit_minutes=? WHERE id=?");
$stmt->bind_param("ssissii", $title, $description, $subject_id, $lecture_type, $access_level, $view_limit, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Recording updated']);
} else {
    echo json_encode(['error' => 'Database update failed']);
}
$stmt->close();

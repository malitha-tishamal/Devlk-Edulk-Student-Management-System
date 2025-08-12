<?php
session_start();
require_once '../includes/db-conn.php';
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['lecture_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Sanitize input
$id = intval($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';

if ($id <= 0 || !in_array($status, ['active', 'disabled'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

// Update status
$stmt = $conn->prepare("UPDATE meeting_resources SET status = ? WHERE id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("si", $status, $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
$stmt->close();

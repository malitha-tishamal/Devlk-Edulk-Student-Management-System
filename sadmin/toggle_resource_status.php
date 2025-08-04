<?php
session_start();
require_once '../includes/db-conn.php';

header('Content-Type: application/json');

// Check user session as needed
if (!isset($_SESSION['sadmin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['error' => 'Invalid resource ID']);
    exit;
}

// Get current status
$stmt = $conn->prepare("SELECT status FROM recording_resources WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Resource not found']);
    exit;
}
$row = $result->fetch_assoc();
$currentStatus = $row['status'];

// Toggle status
$newStatus = $currentStatus === 'active' ? 'disabled' : 'active';

// Update status
$updateStmt = $conn->prepare("UPDATE recording_resources SET status = ? WHERE id = ?");
$updateStmt->bind_param("si", $newStatus, $id);
if ($updateStmt->execute()) {
    echo json_encode(['status' => $newStatus]);
} else {
    echo json_encode(['error' => 'Failed to update status']);
}
?>

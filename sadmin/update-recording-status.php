<?php
session_start();
require_once '../includes/db-conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['sadmin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['id']) || empty($input['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing id or status']);
    exit();
}

$id = intval($input['id']);
$status = $input['status'];

// Validate status
$allowed_status = ['active', 'disabled'];
if (!in_array($status, $allowed_status, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid status value']);
    exit();
}

// Update query
$stmt = $conn->prepare("UPDATE recordings SET status = ? WHERE id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database prepare error']);
    exit();
}

$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => "Recording status updated to '$status'."]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Database execution error']);
}

$stmt->close();
$conn->close();

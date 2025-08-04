<?php
session_start();
require_once '../includes/db-conn.php';

header('Content-Type: application/json');

// Check if user is logged in as superadmin
if (!isset($_SESSION['sadmin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    http_response_code(401);
    exit();
}

// Get raw POST data and decode JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || !is_numeric($input['id'])) {
    echo json_encode(['error' => 'Invalid resource ID']);
    http_response_code(400);
    exit();
}

$resourceId = intval($input['id']);

// Prepare and execute delete query
$stmt = $conn->prepare("DELETE FROM recording_resources WHERE id = ?");
if (!$stmt) {
    echo json_encode(['error' => 'Database error: ' . $conn->error]);
    http_response_code(500);
    exit();
}
$stmt->bind_param("i", $resourceId);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Resource deleted']);
} else {
    echo json_encode(['error' => 'Failed to delete resource']);
}

$stmt->close();
$conn->close();

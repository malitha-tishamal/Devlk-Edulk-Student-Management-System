<?php
session_start();
require_once '../includes/db-conn.php';

header('Content-Type: application/json');
if (!isset($_SESSION['sadmin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM recordings WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
    echo json_encode(['error' => 'Recording not found']);
    exit;
}

echo json_encode($data);

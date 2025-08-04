<?php
session_start();
require_once '../includes/db-conn.php';

// Only accept JSON requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents("php://input"), true);
$recordingId = isset($input['id']) ? intval($input['id']) : 0;

if ($recordingId <= 0) {
    echo json_encode(['error' => 'Invalid recording ID']);
    exit();
}

// Update download count
$update = $conn->prepare("UPDATE recordings SET download_count = download_count + 1 WHERE id = ?");
$update->bind_param("i", $recordingId);
$update->execute();

if ($update->affected_rows > 0) {
    // Fetch new count
    $countStmt = $conn->prepare("SELECT download_count FROM recordings WHERE id = ?");
    $countStmt->bind_param("i", $recordingId);
    $countStmt->execute();
    $result = $countStmt->get_result();
    $row = $result->fetch_assoc();

    echo json_encode(['count' => intval($row['download_count'])]);
    $countStmt->close();
} else {
    echo json_encode(['error' => 'Download count update failed']);
}

$update->close();
$conn->close();
?>

<?php
session_start();
require_once 'includes/db-conn.php';

// Ensure it's a POST request with JSON data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['id']) || !is_numeric($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or missing video ID']);
    exit;
}

$videoId = intval($data['id']);

// Update play count
$sql = "UPDATE recordings SET play_count = play_count + 1 WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $videoId);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    // Fetch new play count
    $result = $conn->query("SELECT play_count FROM recordings WHERE id = $videoId");
    $row = $result->fetch_assoc();
    echo json_encode(['count' => intval($row['play_count'])]);
} else {
    echo json_encode(['error' => 'Failed to update play count']);
}
?>

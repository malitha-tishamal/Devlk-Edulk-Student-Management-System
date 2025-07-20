<?php
session_start();
require_once '../includes/db-conn.php';

header('Content-Type: application/json');

$meeting_id = isset($_GET['meeting_id']) ? intval($_GET['meeting_id']) : 0;
if ($meeting_id <= 0) {
    echo json_encode([]);
    exit();
}

$sql = "SELECT user_name, user_role, message, created_at FROM meeting_chat WHERE meeting_id = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $meeting_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);

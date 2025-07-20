<?php
require_once '../includes/db-conn.php';

$meeting_id = intval($_GET['meeting_id'] ?? 0);
if ($meeting_id <= 0) {
    echo json_encode([]);
    exit();
}

$sql = "SELECT resource_type, resource_data FROM meeting_resources WHERE meeting_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $meeting_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $type = $row['resource_type'];
    $filePath = $row['resource_data']; // e.g., uploads/meeting_resources/filename.png

    // Build response
    $data[] = [
        'type' => $type,
        'url' => $filePath,
        'name' => basename($filePath)
    ];
}

echo json_encode($data);

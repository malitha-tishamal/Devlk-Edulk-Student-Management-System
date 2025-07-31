<?php
require_once '../includes/db-conn.php';

$meeting_id = intval($_GET['meeting_id'] ?? 0);
if ($meeting_id <= 0) {
    echo json_encode([]);
    exit();
}

$sql = "SELECT id, resource_type, resource_data, status FROM meeting_resources WHERE meeting_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $meeting_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $type = $row['resource_type'];
    $resourceData = $row['resource_data'];

    $data[] = [
        'id' => $row['id'], // ✅ important for delete
        'type' => $type,
        'url' => $resourceData,
        'name' => basename($resourceData),
        'status' => $row['status']
    ];
}

echo json_encode($data);
?>

<?php
require_once '../includes/db-conn.php';
$data = [];

$result = $conn->query("SELECT * FROM meetings ORDER BY date DESC, start_time DESC");
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode($data);
$conn->close();
?>

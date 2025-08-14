<?php
session_start();
require_once '../includes/db-conn.php';

// Check if lecturer is logged in
if (empty($_SESSION['lecture_id'])) {
    echo json_encode([]);
    exit();
}

// Get logged-in lecturer's name
$stmt = $conn->prepare("SELECT name FROM lectures WHERE id = ?");
$stmt->bind_param("i", $_SESSION['lecture_id']);
$stmt->execute();
$result = $stmt->get_result();
$lecturer = $result->fetch_assoc();

if (!$lecturer) {
    echo json_encode([]);
    exit();
}

$lecturer_name = $lecturer['name'];

$data = [];

// Fetch meetings created by this lecturer
$query = "
    SELECT * 
    FROM meetings 
    WHERE created_by = ?
    ORDER BY date DESC, start_time DESC
";
$stmt2 = $conn->prepare($query);
$stmt2->bind_param("s", $lecturer_name);
$stmt2->execute();
$res2 = $stmt2->get_result();

while ($row = $res2->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);

$stmt->close();
$stmt2->close();
$conn->close();
?>

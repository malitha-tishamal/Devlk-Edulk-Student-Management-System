<?php
session_start();
require_once '../includes/db-conn.php';

if (!isset($_SESSION['sadmin_id'])) {
    echo json_encode([]);
    exit();
}

// Get logged-in admin's name
$stmt = $conn->prepare("SELECT name FROM sadmins WHERE id = ?");
$stmt->bind_param("i", $_SESSION['sadmin_id']);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$admin_name = $admin['name'] ?? '';

$data = [];

// Fetch meetings created by this admin
$query = "
    SELECT * 
    FROM meetings 
    WHERE created_by = ?
    ORDER BY date DESC, start_time DESC
";
$stmt2 = $conn->prepare($query);
$stmt2->bind_param("s", $admin_name);
$stmt2->execute();
$res2 = $stmt2->get_result();

while ($row = $res2->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
$conn->close();
?>

<?php
require_once '../includes/db-conn.php';
session_start();

header("Content-Type: application/json");

$title = $_POST['title'] ?? '';
$date = $_POST['date'] ?? '';
$start_time = $_POST['start_time'] ?? '';
$zoom_link = $_POST['zoom_link'] ?? '';
$subject = $_POST['subject'] ?? '';
$link_expiry_status = $_POST['link_expiry_status'] ?? 'permanent';
$status = 'active'; // Always default to active

// Get creator name and role
$user_name = '';
$user_role = '';

if (isset($_SESSION['sadmin_id'])) {
    $id = $_SESSION['sadmin_id'];
    $result = $conn->query("SELECT name FROM sadmins WHERE id = $id");
    $user_name = $result->fetch_assoc()['name'] ?? '';
    $user_role = 'admin';
} elseif (isset($_SESSION['admin_id'])) {
    $id = $_SESSION['admin_id'];
    $result = $conn->query("SELECT name FROM admins WHERE id = $id");
    $user_name = $result->fetch_assoc()['name'] ?? '';
    $user_role = 'batch representative';
} elseif (isset($_SESSION['lecturer_id'])) {
    $id = $_SESSION['lecturer_id'];
    $result = $conn->query("SELECT name FROM lecturers WHERE id = $id");
    $user_name = $result->fetch_assoc()['name'] ?? '';
    $user_role = 'lecturer';
}

// Validation
if ($title && $date && $start_time && $zoom_link && $subject) {
    $stmt = $conn->prepare("INSERT INTO meetings (title, date, start_time, zoom_link, created_by, role, status, subject, link_expiry_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("sssssssss", $title, $date, $start_time, $zoom_link, $user_name, $user_role, $status, $subject, $link_expiry_status);

    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
}

$conn->close();
?>

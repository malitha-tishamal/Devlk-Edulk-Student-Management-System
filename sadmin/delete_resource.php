<?php
session_start();
require_once '../includes/db-conn.php';
header('Content-Type: application/json');

// Check if logged in (you can customize this based on role)
if (!isset($_SESSION['sadmin_id']) && !isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Resource ID missing']);
    exit;
}

$resource_id = intval($_POST['id']);

// Get resource details (to remove file if needed)
$stmt = $conn->prepare("SELECT resource_type, resource_data FROM meeting_resources WHERE id = ?");
$stmt->bind_param("i", $resource_id);
$stmt->execute();
$result = $stmt->get_result();
$resource = $result->fetch_assoc();
$stmt->close();

if (!$resource) {
    echo json_encode(['success' => false, 'message' => 'Resource not found']);
    exit;
}

// If it's a file, delete from server
if ($resource['resource_type'] === 'file') {
    $file_path = '../uploads/meeting_resources/' . $resource['resource_data'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

// Delete from database
$stmt = $conn->prepare("DELETE FROM meeting_resources WHERE id = ?");
$stmt->bind_param("i", $resource_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Delete failed']);
}
?>

<?php
session_start();
require_once '../includes/db-conn.php';

if (!isset($_SESSION['sadmin_id'])) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit();
}

$meeting_id = intval($_POST['meeting_id'] ?? 0);
$resource_type = $_POST['resource_type'] ?? '';
$resource_data = trim($_POST['resource_data'] ?? '');

if ($meeting_id <= 0 || empty($resource_type) || empty($resource_data)) {
  echo json_encode(['success' => false, 'message' => 'Invalid input']);
  exit();
}

$uploaded_by = $_SESSION['sadmin_id'];

$sql = "INSERT INTO meeting_resources (meeting_id, resource_type, resource_data, uploaded_by) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issi", $meeting_id, $resource_type, $resource_data, $uploaded_by);

if ($stmt->execute()) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'message' => 'DB insert failed']);
}

$stmt->close();
?>

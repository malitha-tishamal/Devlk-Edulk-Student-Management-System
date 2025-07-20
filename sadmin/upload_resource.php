<?php
session_start();
require_once '../includes/db-conn.php';

if (!isset($_SESSION['sadmin_id'])) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit();
}

$meeting_id = intval($_POST['meeting_id'] ?? 0);
if ($meeting_id <= 0 || !isset($_FILES['file'])) {
  echo json_encode(['success' => false, 'message' => 'Invalid input']);
  exit();
}

$uploadDir = '../uploads/meeting_resources/';
if (!is_dir($uploadDir)) {
  mkdir($uploadDir, 0755, true);
}

$file = $_FILES['file'];
$filename = basename($file['name']);
$targetFile = $uploadDir . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

if (move_uploaded_file($file['tmp_name'], $targetFile)) {
  $resource_data = substr($targetFile, 3); // remove ../ from path to store relative path, adjust as needed
  $resource_type = 'file';
  $uploaded_by = $_SESSION['sadmin_id'];

  $sql = "INSERT INTO meeting_resources (meeting_id, resource_type, resource_data, uploaded_by) VALUES (?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("issi", $meeting_id, $resource_type, $resource_data, $uploaded_by);
  if ($stmt->execute()) {
    echo json_encode(['success' => true]);
  } else {
    unlink($targetFile);
    echo json_encode(['success' => false, 'message' => 'DB insert failed']);
  }
  $stmt->close();
} else {
  echo json_encode(['success' => false, 'message' => 'File upload failed']);
}

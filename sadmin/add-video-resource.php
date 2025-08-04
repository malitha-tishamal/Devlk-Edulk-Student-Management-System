<?php
session_start();
require_once '../includes/db-conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['sadmin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$recording_id = intval($_POST['recording_id'] ?? 0);
$type = $_POST['type'] ?? '';
$title = trim($_POST['title'] ?? '');

if (!$recording_id || !$title || !in_array($type, ['file','link'])) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$file_path = null;
$link_url = null;
$status = 'active'; // default status

if ($type === 'file') {
    if (!isset($_FILES['resource_file']) || $_FILES['resource_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'File upload failed']);
        exit;
    }

    $uploadDir = '../uploads/resources/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $filename = basename($_FILES['resource_file']['name']);
    $targetFile = $uploadDir . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

    if (move_uploaded_file($_FILES['resource_file']['tmp_name'], $targetFile)) {
        $file_path = substr($targetFile, 3); // Save path as relative
    } else {
        echo json_encode(['error' => 'Failed to move uploaded file']);
        exit;
    }
} else {
    $link_url = filter_var($_POST['link_url'], FILTER_VALIDATE_URL);
    if (!$link_url) {
        echo json_encode(['error' => 'Invalid URL']);
        exit;
    }
}

$stmt = $conn->prepare("INSERT INTO recording_resources (recording_id, type, title, file_path, link_url, status) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssss", $recording_id, $type, $title, $file_path, $link_url, $status);

if ($stmt->execute()) {
    echo json_encode([
        'success' => 'Resource added',
        'recording_id' => $recording_id
    ]);
} else {
    echo json_encode(['error' => 'Database error']);
}

$stmt->close();
?>

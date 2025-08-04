<?php
session_start();
require_once '../includes/db-conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['sadmin_id'])) {
    echo json_encode(['Unauthorized access. Please login.']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || !is_numeric($input['id'])) {
    echo json_encode(['Invalid recording ID']);
    exit;
}

$recording_id = intval($input['id']);

// First, fetch the file paths to delete the physical files
$stmt = $conn->prepare("SELECT video_path, thumbnail_path FROM recordings WHERE id = ?");
$stmt->bind_param("i", $recording_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['Recording not found']);
    exit;
}

$recording = $result->fetch_assoc();
$stmt->close();

// Delete video file
if (!empty($recording['video_path']) && file_exists("../" . $recording['video_path'])) {
    unlink("../" . $recording['video_path']);
}

// Delete thumbnail file
if (!empty($recording['thumbnail_path']) && file_exists("../" . $recording['thumbnail_path'])) {
    unlink("../" . $recording['thumbnail_path']);
}

// Delete record from DB
$stmt = $conn->prepare("DELETE FROM recordings WHERE id = ?");
$stmt->bind_param("i", $recording_id);
if ($stmt->execute()) {
    echo json_encode(['Recording deleted successfully.']);
} else {
    echo json_encode(['Failed to delete the recording. Please try again later.']);
}
$stmt->close();

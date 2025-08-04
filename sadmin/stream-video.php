<?php
session_start();
require_once '../includes/db-conn.php';

if (!isset($_SESSION['sadmin_id']) && !isset($_SESSION['student_id'])) {
    http_response_code(403);
    exit('Access denied');
}

$file = $_GET['file'] ?? '';
$file = basename($file);
$videoDir = realpath(__DIR__ . '/../uploads/recordings/') . '/';
$fullPath = $videoDir . $file;

if (!file_exists($fullPath)) {
    http_response_code(404);
    exit('File not found');
}

// 🔁 Update play count only if user is student
if (isset($_SESSION['student_id'])) {
    $stmt = $conn->prepare("UPDATE recordings SET play_count = play_count + 1 WHERE video_path = ?");
    $stmt->bind_param("s", $videoPath);
    $videoPath = 'uploads/recordings/' . $file;
    $stmt->execute();
    $stmt->close();
}

// Serve video
header('Content-Type: video/mp4');
header('Content-Length: ' . filesize($fullPath));
header('Content-Disposition: inline; filename="' . $file . '"');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Expires: 0');
header('Accept-Ranges: none'); // block IDM

readfile($fullPath);
exit;


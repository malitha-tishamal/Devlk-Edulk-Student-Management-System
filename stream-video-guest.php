<?php
session_start();
require_once 'includes/db-conn.php';

// =====================
// CONFIG
// =====================
$maxPlays = 3; // max plays per user
$file = basename($_GET['file'] ?? '');
$videoDir = realpath(__DIR__ . '/uploads/recordings/') . '/';
$fullPath = $videoDir . $file;

// =====================
// VALIDATE FILE
// =====================
if (!$file || !file_exists($fullPath)) {
    http_response_code(404);
    exit('File not found');
}

// =====================
// CHECK LOGIN
// =====================
if (!isset($_SESSION['student_id'])) {
    http_response_code(403);
    exit('⛔ You must log in to watch this video.');
}

$user_id = $_SESSION['student_id'];

// =====================
// CHECK PLAY COUNT
// =====================
$stmt = $conn->prepare("SELECT play_count FROM video_watch_counts WHERE user_id=? AND video_id=?");
$stmt->bind_param("is", $user_id, $file);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

$currentCount = $res['play_count'] ?? 0;

if ($currentCount >= $maxPlays) {
    http_response_code(403);
    exit('⛔ You have reached the maximum allowed plays for this video.');
}

// =====================
// UPDATE PLAY COUNT
// =====================
$stmt = $conn->prepare("INSERT INTO video_watch_counts (user_id, video_id, play_count)
    VALUES (?, ?, 1)
    ON DUPLICATE KEY UPDATE play_count = play_count + 1, last_play=NOW()");
$stmt->bind_param("is", $user_id, $file);
$stmt->execute();

// =====================
// STREAM VIDEO SAFELY
// =====================
header('Content-Type: video/mp4');
header('Content-Length: ' . filesize($fullPath));
header('Content-Disposition: inline; filename="' . $file . '"');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Accept-Ranges: none'); // block resume/download managers

readfile($fullPath);
exit;

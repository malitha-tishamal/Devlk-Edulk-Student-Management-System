<?php
session_start();
require_once 'includes/db-conn.php';

if (!isset($_SESSION['student_id'])) {
    http_response_code(403);
    exit('Access denied');
}

$file = basename($_GET['file'] ?? '');
$videoDir = realpath(__DIR__ . '/uploads/recordings/') . '/';
$fullPath = $videoDir . $file;

if (!file_exists($fullPath)) {
    http_response_code(404);
    exit('File not found');
}


// Serve video safely
header('Content-Type: video/mp4');
header('Content-Length: ' . filesize($fullPath));
header('Content-Disposition: inline; filename="' . $file . '"');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Accept-Ranges: none'); // blocks partial requests

readfile($fullPath);
exit;



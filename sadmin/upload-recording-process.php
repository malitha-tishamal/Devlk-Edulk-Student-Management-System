<?php
session_start();
require_once '../includes/db-conn.php';

if (!isset($_SESSION['sadmin_id'])) {
    http_response_code(403);
    echo "Access denied";
    exit();
}

// Sanitize & validate inputs
$subject_id = intval($_POST['subject_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$lecture_type = trim($_POST['lecture_type'] ?? 'Upload');
$tags = trim($_POST['tags'] ?? '');
$access_level = trim($_POST['access_level'] ?? 'public');
$status = trim($_POST['status'] ?? 'active');
$release_time = $_POST['release_time'] ?? date("Y-m-d H:i:s"); // Default to now if not set
$view_limit = intval($_POST['view_limit_minutes'] ?? 0);
$created_by = $_SESSION['sadmin_id'];
$role = 'superadmin';

// Paths
$upload_dir = '../uploads/recordings/';
$thumb_dir = '../uploads/thumbnails/';
$video_path = '';
$thumb_path = '';

// Ensure folders exist
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
if (!is_dir($thumb_dir)) mkdir($thumb_dir, 0777, true);

// ==== Video Upload ====
if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
    $video_ext = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));
    if (!in_array($video_ext, ['mp4'])) {
        http_response_code(400);
        echo "Only MP4 video files are allowed.";
        exit();
    }

    $video_name = uniqid('vid_') . '.' . $video_ext;
    $video_tmp = $_FILES['video_file']['tmp_name'];
    $video_dest = $upload_dir . $video_name;

    if (!move_uploaded_file($video_tmp, $video_dest)) {
        http_response_code(500);
        echo "Failed to upload video file.";
        exit();
    }

    $video_path = str_replace('../', '', $video_dest);
} else {
    http_response_code(400);
    echo "Video file is required.";
    exit();
}

// ==== Thumbnail Upload (Optional) ====
if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
    $thumb_ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
    if (!in_array($thumb_ext, ['jpg', 'jpeg', 'png'])) {
        http_response_code(400);
        echo "Thumbnail must be JPG or PNG.";
        exit();
    }

    $thumb_name = uniqid('thumb_') . '.' . $thumb_ext;
    $thumb_tmp = $_FILES['thumbnail']['tmp_name'];
    $thumb_dest = $thumb_dir . $thumb_name;

    if (!move_uploaded_file($thumb_tmp, $thumb_dest)) {
        http_response_code(500);
        echo "Failed to upload thumbnail.";
        exit();
    }

    $thumb_path = str_replace('../', '', $thumb_dest);
}

// ==== Insert into DB ====
$stmt = $conn->prepare("
    INSERT INTO recordings 
    (subject_id, title, description, lecture_type, tags, access_level, status, release_time, video_path, thumbnail_path, view_limit_minutes, created_by, role)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    http_response_code(500);
    echo "Prepare failed: " . $conn->error;
    exit();
}

$stmt->bind_param(
    "isssssssssiss",
    $subject_id,
    $title,
    $description,
    $lecture_type,
    $tags,
    $access_level,
    $status,
    $release_time,
    $video_path,
    $thumb_path,
    $view_limit,
    $created_by,
    $role
);

if ($stmt->execute()) {
    echo "Lecture uploaded successfully!";
} else {
    http_response_code(500);
    echo "Database error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

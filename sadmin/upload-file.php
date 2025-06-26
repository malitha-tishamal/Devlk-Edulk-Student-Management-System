<?php
// upload-file.php
session_start();
require_once '../includes/db-conn.php';

if (!isset($_SESSION['sadmin_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $semester = $_POST['semester'] ?? '';
    $titles = $_POST['title'] ?? [];
    $subject_ids = $_POST['subject_id'] ?? [];
    $categories = $_POST['category'] ?? [];
    $files = $_FILES['file'];

    $uploadDir = '../uploads/';
    $uploadSuccessCount = 0;
    $errors = [];

    foreach ($titles as $index => $title) {
        $title = trim($title);
        $subject_id = intval($subject_ids[$index] ?? 0);
        $category = $categories[$index] ?? '';

        if ($title && $subject_id && $category && isset($files['tmp_name'][$index]) && $files['error'][$index] === UPLOAD_ERR_OK) {
            $originalName = basename($files['name'][$index]);
            $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '', $originalName);
            $uniqueName = time() . '_' . uniqid() . '_' . $safeName;
            $destination = $uploadDir . $uniqueName;

            if (move_uploaded_file($files['tmp_name'][$index], $destination)) {
                $stmt = $conn->prepare("INSERT INTO tuition_files (title, subject_id, category, filename, status, uploaded_at) VALUES (?, ?, ?, ?, 'active', NOW())");
                $stmt->bind_param("siss", $title, $subject_id, $category, $uniqueName);
                $stmt->execute();
                $stmt->close();
                $uploadSuccessCount++;
            } else {
                $errors[] = "Failed to upload: $originalName";
            }
        } else {
            $errors[] = "Missing data in section " . ($index + 1);
        }
    }

    $redirect = "manage-files.php?semester=" . urlencode($semester);
    if ($uploadSuccessCount > 0) {
        $msg = "$uploadSuccessCount file(s) uploaded.";
        if (!empty($errors)) {
            $msg .= " Errors: " . implode(", ", $errors);
        }
        header("Location: $redirect&upload=success&msg=" . urlencode($msg));
    } else {
        header("Location: $redirect&upload=error&msg=" . urlencode(implode(", ", $errors)));
    }
    exit();
}

header("Location: manage-files.php");
exit();

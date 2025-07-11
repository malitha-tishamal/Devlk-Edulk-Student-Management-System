<?php
session_start();
require_once '../includes/db-conn.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $semester = $_POST['semester'] ?? '';
    $titles = $_POST['title'] ?? [];
    $subject_ids = $_POST['subject_id'] ?? [];
    $categories = $_POST['category'] ?? [];
    $files = $_FILES['file'];

    if (empty($semester)) {
        header("Location: manage-files.php?upload=error&msg=Missing+semester+selection");
        exit();
    }

    if (empty($titles) || empty($subject_ids) || empty($categories) || empty($files['name'][0])) {
        header("Location: manage-files.php?semester=" . urlencode($semester) . "&upload=error&msg=Incomplete+form+data");
        exit();
    }

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
                $errors[] = "Failed to upload file: " . $originalName;
            }
        } else {
            $errorCode = $files['error'][$index] ?? 'unknown';
            $errors[] = "Upload error in section " . ($index + 1) . " (Error code: $errorCode)";
        }
    }

    $redirect = "manage-files.php?semester=" . urlencode($semester);
    if ($uploadSuccessCount > 0) {
        $msg = "$uploadSuccessCount file(s) uploaded successfully.";
        if (!empty($errors)) {
            $msg .= " But some errors occurred: " . implode(" | ", $errors);
        }
        header("Location: $redirect&upload=success&msg=" . urlencode($msg));
    } else {
        $msg = !empty($errors) ? implode(" | ", $errors) : "Unknown error. No files were uploaded.";
        header("Location: $redirect&upload=error&msg=" . urlencode($msg));
    }
    exit();
}

header("Location: manage-files.php");
exit();

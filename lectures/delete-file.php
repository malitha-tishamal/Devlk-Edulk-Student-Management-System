<?php
// delete-file.php
session_start();
require_once '../includes/db-conn.php';

if (!isset($_SESSION['lecture_id'])) {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $fileId = intval($_GET['id']);
    $semester = $_GET['semester'] ?? '';

    // Step 1: Fetch the filename from DB
    $stmt = $conn->prepare("SELECT filename FROM tuition_files WHERE id = ?");
    $stmt->bind_param("i", $fileId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($file = $result->fetch_assoc()) {
        $filePath = '../uploads/' . $file['filename'];

        // Step 2: Delete file from server
        if (file_exists($filePath)) {
            unlink($filePath); // Delete physical file
        }

        // Step 3: Delete from database
        $deleteStmt = $conn->prepare("DELETE FROM tuition_files WHERE id = ?");
        $deleteStmt->bind_param("i", $fileId);
        if ($deleteStmt->execute()) {
            $msg = "File deleted successfully.";
            $status = "success";
        } else {
            $msg = "Failed to delete file record from database.";
            $status = "error";
        }
        $deleteStmt->close();
    } else {
        $msg = "File not found in database.";
        $status = "error";
    }

    $stmt->close();
    $conn->close();

    // Redirect back with message
    header("Location: manage-files.php?semester=" . urlencode($semester) . "&upload=$status&msg=" . urlencode($msg));
    exit();
}

// Fallback redirect
header("Location: manage-files.php");
exit();

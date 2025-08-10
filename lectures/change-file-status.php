<?php
// change-file-status.php
session_start();
require_once '../includes/db-conn.php';

// Check login: You check for lecture_id session; is this correct? 
// (If this script is for superadmins, maybe check 'sadmin_id' or adjust accordingly)
if (!isset($_SESSION['lecture_id'])) {
    header("Location: ../index.php");
    exit();
}

// Validate 'id' and 'status' GET parameters
if (!isset($_GET['id'], $_GET['status']) || !is_numeric($_GET['id'])) {
    header("Location: manage-files.php");
    exit();
}

$file_id = intval($_GET['id']);
$new_status = $_GET['status'];

// Allow only specific statuses
$allowed_statuses = ['active', 'inactive', 'disabled'];
if (!in_array($new_status, $allowed_statuses, true)) {
    // Redirect to manage-files.php (with semester if exists)
    $redirect_url = "manage-files.php";
    if (!empty($_GET['semester'])) {
        $redirect_url .= "?semester=" . urlencode($_GET['semester']);
    }
    header("Location: $redirect_url");
    exit();
}

// Update status in DB
$stmt = $conn->prepare("UPDATE tuition_files SET status = ? WHERE id = ?");
if ($stmt === false) {
    // Prepare failed, handle error (optional)
    die("Database error: " . $conn->error);
}
$stmt->bind_param("si", $new_status, $file_id);
$stmt->execute();
$stmt->close();

// Redirect back to manage-files.php with semester parameter if provided
$redirect_url = "manage-files.php";
if (!empty($_GET['semester'])) {
    $redirect_url .= "?semester=" . urlencode($_GET['semester']);
}

header("Location: $redirect_url");
exit();

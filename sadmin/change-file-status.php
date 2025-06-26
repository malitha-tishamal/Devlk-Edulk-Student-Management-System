<?php
// change-file-status.php
session_start();
require_once '../includes/db-conn.php';

if (!isset($_SESSION['sadmin_id'])) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id'], $_GET['status']) || !is_numeric($_GET['id'])) {
    header("Location: test.php");
    exit();
}

$file_id = intval($_GET['id']);
$new_status = $_GET['status'];

if (!in_array($new_status, ['active', 'inactive'])) {
    header("Location: test.php");
    exit();
}

// Update status in DB
$stmt = $conn->prepare("UPDATE tuition_files SET status = ? WHERE id = ?");
$stmt->bind_param("si", $new_status, $file_id);
$stmt->execute();
$stmt->close();

// Redirect back with semester if provided
$redirect_url = "test.php";
if (isset($_GET['semester']) && !empty($_GET['semester'])) {
    $redirect_url .= "?semester=" . urlencode($_GET['semester']);
}

header("Location: $redirect_url");
exit();

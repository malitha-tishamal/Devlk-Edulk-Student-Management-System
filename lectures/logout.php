<?php
session_start();
require_once '../includes/db-conn.php';
$logout_time = date("Y-m-d H:i:s");

$stmt = $conn->prepare("UPDATE lectures_logs SET logout_time=? WHERE session_id=?");
$stmt->bind_param("ss", $logout_time, session_id());
$stmt->execute();

session_destroy();
header("Location: ../index.php");
exit();
?>

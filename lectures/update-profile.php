<?php
require_once '../includes/db-conn.php';
session_start();

// Check if the sadmin is logged in
if (!isset($_SESSION['lecture_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sadmin_id = $_SESSION['lecture_id'];

    // Get and sanitize inputs
    $name      = trim($_POST['name']);
    $nic       = trim($_POST['nic']);
    $email     = trim($_POST['email']);
    $mobile    = trim($_POST['mobile']);

    // Basic validation
    if (empty($name) || empty($email)) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Name and Email are required.';
        header("Location: user-profile.php");
        exit();
    }

    // Prepare the update statement
    $query = "UPDATE lectures 
              SET name = ?, nic = ?, email = ?, mobile = ?
              WHERE id = ?";

    $stmt = $conn->prepare($query);

    if (!$stmt) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Prepare failed: ' . $conn->error;
        header("Location: user-profile.php");
        exit();
    }

    $stmt->bind_param("ssssi", $name, $nic, $email, $mobile, $sadmin_id);

    if ($stmt->execute()) {
        $_SESSION['status'] = 'success';
        $_SESSION['message'] = 'Profile updated successfully.';
    } else {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Failed to update profile. Try again.';
    }

    $stmt->close();
    $conn->close();

    header("Location: user-profile.php");
    exit();
} else {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Invalid request method.';
    header("Location: user-profile.php");
    exit();
}
?>

<?php
require_once 'includes/db-conn.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $former_student_id = $_SESSION['student_id'];

    // Get and sanitize inputs
    $name       = trim($_POST['name']);
    $regno      = trim($_POST['regno']);
    $nic        = trim($_POST['nic']);
    $email      = trim($_POST['email']);
    $mobile     = trim($_POST['mobile']);
    $gender     = trim($_POST['gender']);
    $address    = trim($_POST['address']);
    $nowstatus  = trim($_POST['nowstatus']);
    $birthday   = trim($_POST['birthday']);
    $batch_year = trim($_POST['batch_year']);

    // Validation (basic)
    if (empty($name) || empty($email) || empty($regno)) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Name, Reg No and Email are required.';
        header("Location: user-profile.php");
        exit();
    }

    // Prepare the update statement
    $query = "UPDATE students 
              SET name = ?, regno = ?, nic = ?, email = ?, mobile = ?, gender = ?, address = ?, nowstatus = ?, birthday = ?, batch_year = ?
              WHERE id = ?";

    $stmt = $conn->prepare($query);

    if (!$stmt) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Prepare failed: ' . $conn->error;
        header("Location: user-profile.php");
        exit();
    }

    $stmt->bind_param(
        "ssssssssssi", 
        $name, 
        $regno, 
        $nic, 
        $email, 
        $mobile, 
        $gender, 
        $address, 
        $nowstatus, 
        $birthday, 
        $batch_year, 
        $former_student_id
    );

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

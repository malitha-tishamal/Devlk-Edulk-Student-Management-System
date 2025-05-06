<?php
session_start();
require_once 'includes/db-conn.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['student_id'])) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Unauthorized access!';
        header("Location: user-profile.php");
        exit();
    }

    $user_id = $_SESSION['student_id'];
    $linkedin = trim($_POST['linkedin']);
    $blog = trim($_POST['blog']);
    $facebook = trim($_POST['facebook']);
    $github = trim($_POST['github']);
    

    // Update user details in the database
    $sql = "UPDATE students SET linkedin = ?, blog = ?, facebook = ?, github = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssssi", $linkedin, $blog, $facebook, $github, $user_id);
        if ($stmt->execute()) {
            $_SESSION['status'] = 'success';
            $_SESSION['message'] = 'Profile Social Media successfully!';
        } else {
            $_SESSION['status'] = 'error';
            $_SESSION['message'] = 'Failed to update Links';
        }
        $stmt->close();
    } else {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Database error!';
    }

    // Redirect back to profile page
    header("Location: user-profile.php");
    exit();
}
?>

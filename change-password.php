<?php
session_start();
require_once "includes/db-conn.php"; // Ensure the DB connection

// Check if admin is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php"); // Redirect if not logged in
    exit();
}

$user_id = $_SESSION['former_student_id'];  // Assuming the session stores admin's ID for updating their profile

// Handle password change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation: Ensure new password and confirm password match
    if ($new_password !== $confirm_password) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'New password and confirmation do not match.';
        header("Location: user-profile.php");
        exit();
    }

    // Validate new password strength (e.g., min 8 characters)
    if (strlen($new_password) < 8) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Password must be at least 8 characters long.';
        header("Location: user-profile.php");
        exit();
    }

    // Fetch current admin's data from database
    $sql = "SELECT * FROM students WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();

        // Verify the current password
        if (password_verify($current_password, $admin['password'])) {
            // Hash the new password
            $hashed_new_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Update the password in the admins table (assuming admin's password is being updated)
            $update_sql = "UPDATE students SET password = ? WHERE id = ?";
            if ($update_stmt = $conn->prepare($update_sql)) {
                $update_stmt->bind_param("si", $hashed_new_password, $user_id);
                if ($update_stmt->execute()) {
                    // Success message
                    $_SESSION['status'] = 'success';
                    $_SESSION['message'] = 'Password changed successfully.';
                    header("Location: user-profile.php");
                    exit();
                } else {
                    $_SESSION['status'] = 'error';
                    $_SESSION['message'] = 'Failed to update password in the database.';
                }
            }
        } else {
            $_SESSION['status'] = 'error';
            $_SESSION['message'] = 'Current password is incorrect.';
        }
        $stmt->close();
    }

    // Close the connection
    $conn->close();

    // Redirect to profile page with error message
    header("Location: user-profile.php");
    exit();
}
?>

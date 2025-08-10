<?php
require_once "../includes/db-conn.php";

// Approve user
if (isset($_GET['approve_id'])) {
    $user_id = $_GET['approve_id'];
    $sql = "UPDATE students SET status = 'approved' WHERE id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            header("Location: manage-students.php?message=User approved successfully!&msg_type=success");
        } else {
            header("Location: manage-students.php?message=Error approving user.&msg_type=danger");
        }
        $stmt->close();
    }
}

// Disable user
if (isset($_GET['disable_id'])) {
    $user_id = $_GET['disable_id'];
    $sql = "UPDATE students SET status = 'rejected' WHERE id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            header("Location: manage-students.php?message=User disabled successfully!&msg_type=success");
        } else {
            header("Location: manage-students.php?message=Error disabling user.&msg_type=danger");
        }
        $stmt->close();
    }
}

// Delete user
if (isset($_GET['delete_id'])) {
    $user_id = $_GET['delete_id'];
    $sql = "DELETE FROM students WHERE id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            header("Location: manage-students.php?message=User deleted successfully!&msg_type=success");
        } else {
            header("Location: manage-students.php?message=Error deleting user.&msg_type=danger");
        }
        $stmt->close();
    }
}



// Check for the appropriate action (approve, disable, delete)
if (isset($_GET['approve_id'])) {
    $userId = $_GET['approve_id'];
    // Your code to approve the user...
    // After success, redirect back to the previous page with a refresh
    header("Location: manage-students.php");
    exit();
}

if (isset($_GET['disable_id'])) {
    $userId = $_GET['disable_id'];
    // Your code to disable the user...
    // After success, redirect back to the previous page with a refresh
    header("Location: manage-students.php");
    exit();
}

if (isset($_GET['delete_id'])) {
    $userId = $_GET['delete_id'];
    // Your code to delete the user...
    // After success, redirect back to the previous page with a refresh
    header("Location: manage-students.php");
    exit();
}
$conn->close();
?>

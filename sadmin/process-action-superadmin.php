<?php
require_once "../includes/db-conn.php";

// Approve user
if (isset($_GET['approve_id'])) {
    $user_id = $_GET['approve_id'];
    $sql = "UPDATE admins SET status = 'approved' WHERE id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            header("Location: manage-super-admins.php?message=User approved successfully!&msg_type=success");
        } else {
            header("Location: manage-super-admins.php?message=Error approving user.&msg_type=danger");
        }
        $stmt->close();
    }
}

// Disable user
if (isset($_GET['disable_id'])) {
    $user_id = $_GET['disable_id'];
    $sql = "UPDATE admins SET status = 'disabled' WHERE id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            header("Location: manage-super-admins.php?message=User disabled successfully!&msg_type=success");
        } else {
            header("Location: manage-super-admins.php?message=Error disabling user.&msg_type=danger");
        }
        $stmt->close();
    }
}

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Prevent deleting system superadmin (ID = 1)
    if ($delete_id === 1) {
        header("Location: manage-admins.php?error=superadmin_protected");
        exit();
    }

    // Proceed with deletion
    $stmt = $conn->prepare("DELETE FROM sadmins WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    header("Location: manage-admins.php?success=admin_deleted");
    exit();
}




// Check for the appropriate action (approve, disable, delete)
if (isset($_GET['approve_id'])) {
    $userId = $_GET['approve_id'];
    // Your code to approve the user...
    // After success, redirect back to the previous page with a refresh
    header("Location: manage-super-admins.php");
    exit();
}

if (isset($_GET['disable_id'])) {
    $userId = $_GET['disable_id'];
    // Your code to disable the user...
    // After success, redirect back to the previous page with a refresh
    header("Location: manage-super-admins.php");
    exit();
}

if (isset($_GET['delete_id'])) {
    $userId = $_GET['delete_id'];
    // Your code to delete the user...
    // After success, redirect back to the previous page with a refresh
    header("Location: manage-super-admins.php");
    exit();
}



$conn->close();
?>

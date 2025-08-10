<?php
session_start();
require_once '../includes/db-conn.php'; 

if (!isset($_SESSION['lecture_id'])) {
    header("Location: ../index.php");
    exit();
}

if (isset($_POST['submit'])) {
    $user_id = $_SESSION['lecture_id'];
    
    // Handle file upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
        $fileName = $_FILES['profile_picture']['name'];
        $fileSize = $_FILES['profile_picture']['size'];
        $fileType = $_FILES['profile_picture']['type'];

        // Define allowed file types
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

        if (in_array($fileType, $allowedTypes)) {
            // Generate a unique filename to prevent overwriting
            $newFileName = uniqid() . '-' . basename($fileName);
            $uploadDir = 'uploads/profile_pictures/'; // Folder where images are stored
            $destPath = $uploadDir . $newFileName; // Full file path

            // Move the uploaded file to the desired directory
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                // Store the **full relative path** instead of just the file name
                $filePathToStore = $uploadDir . $newFileName; // "uploads/profile_pictures/filename.jpg"

                // Update the database with the full path
                $sql = "UPDATE lectures SET profile_picture = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $filePathToStore, $user_id);

                if ($stmt->execute()) {
                    $_SESSION['status'] = 'success';
                    $_SESSION['message'] = 'Profile picture updated successfully!';
                } else {
                    $_SESSION['status'] = 'error';
                    $_SESSION['message'] = 'Error updating profile picture.';
                }
                $stmt->close();
            } else {
                $_SESSION['status'] = 'error';
                $_SESSION['message'] = 'Error uploading the image.';
            }
        } else {
            $_SESSION['status'] = 'error';
            $_SESSION['message'] = 'Only JPG, PNG, or GIF files are allowed.';
        }
    } else {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Please select an image to upload.';
    }

    header("Location: user-profile.php"); // Redirect back to the profile page
    exit();
}
?>

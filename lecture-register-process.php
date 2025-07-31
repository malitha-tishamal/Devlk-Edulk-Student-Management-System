<?php
// Start session to store success/error messages
session_start();

// Include database connection
include_once("includes/db-conn.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form inputs and sanitize them
    $name = $_POST['name'];
    $nic = $_POST['nic'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Password hashing

    // Check for duplicate email or NIC
    $checkQuery = "SELECT * FROM lectures WHERE email = ? OR nic = ?";
    if ($stmt = $conn->prepare($checkQuery)) {
        $stmt->bind_param("ss", $email, $nic); // Only two parameters: email and nic
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Duplicate found
            $_SESSION['status'] = 'error';
            $_SESSION['message'] = 'Email or NIC already exists. Please try again with different details.';
            $stmt->close();
            exit();
        }
        $stmt->close();
    }

    // Prepare SQL query to insert admin data into the database (without profile picture)
    $query = "INSERT INTO lectures (name, nic, email, mobile, password) 
              VALUES (?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($query)) {
        // Bind parameters to the SQL query
        $stmt->bind_param("sssss", $name, $nic, $email, $mobile, $password); // Correct bind string

        // Execute the query
        if ($stmt->execute()) {
            // Set session message for success
            $_SESSION['status'] = 'success';
            $_SESSION['message'] = 'Lecture account successfully created!';
            header("Location: index.php");
            exit();
        } else {
            // Set session message for failure
            $_SESSION['status'] = 'error';
            $_SESSION['message'] = 'Failed to create account. Please try again.';
            exit();
        }

        // Close statement and connection
        $stmt->close();
    } else {
        // Set session message for failure if the query preparation fails
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Database error. Please try again.';
        exit();
    }
}
?>

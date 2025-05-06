<?php
// Start the session to store the message
session_start();

// Include the database connection
require_once "../includes/db-conn.php";

// Initialize response variables
$status = '';
$message = '';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form input values
    $nic = trim($_POST["nic"]);
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $mobile = trim($_POST["mobile"]);
    $password = $_POST["password"];

    // Basic Validation
    if (empty($nic) || empty($name) || empty($email) || empty($mobile) || empty($password)) {
        // Error if any field is missing
        $status = 'error';
        $message = 'All fields are required!';
    } else {
        // Hash the password securely
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Check if email or NIC already exists
        $check_sql = "SELECT * FROM users WHERE email = ? OR nic = ?";
        if ($check_stmt = $conn->prepare($check_sql)) {
            $check_stmt->bind_param("ss", $email, $nic);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                // Error if email or NIC is already registered
                $status = 'error';
                $message = 'Email or NIC already registered!';
            } else {
                // Insert new user into the database
                $sql = "INSERT INTO users (nic, name, email, mobile, password) VALUES (?, ?, ?, ?, ?)";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("sssss", $nic, $name, $email, $mobile, $hashed_password);
                    if ($stmt->execute()) {
                        // Success message
                        $status = 'success';
                        $message = 'Account created successfully!';

                        // Store session variables for status and message
                        $_SESSION['status'] = $status;
                        $_SESSION['message'] = $message;

                        // Redirect to index.php after registration
                        header("Location: add-admin.php");
                        exit(); // Stop further script execution
                    } else {
                        // Database error
                        $status = 'error';
                        $message = 'Database error: ' . $conn->error;
                    }
                    $stmt->close();
                }
            }
            $check_stmt->close();
        }
    }

    // If there is an error, store session variables and stay on the current page
    if ($status == 'error') {
        $_SESSION['status'] = $status;
        $_SESSION['message'] = $message;
        header("Location: add-user.php"); // Stay on the same page in case of error
        exit();
    }

    // Close the database connection
    $conn->close();
}
?>

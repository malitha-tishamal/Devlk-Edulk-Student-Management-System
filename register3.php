<?php
session_start();
require_once "includes/db-conn.php";

// Validate required POST fields
$requiredFields = ['username', 'regno', 'nic', 'email', 'gender', 'mobile', 'password'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = ucfirst($field) . " is required.";
        header("Location: pages-batchadmin-signup.php"); // Redirect back to the form page
        exit();
    }
}

// Sanitize and assign variables
$name       = trim($_POST['username']);
$regno      = strtoupper(trim($_POST['regno']));
$nic        = strtoupper(trim($_POST['nic']));
$email      = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$gender     = $_POST['gender'];
$mobile     = trim($_POST['mobile']);
$password   = password_hash($_POST['password'], PASSWORD_DEFAULT);

// NIC validation
if (!preg_match("/^(\d{9}[VXvx]|\d{12})$/", $nic)) {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = "Invalid NIC format.";
    header("Location: pages-batchadmin-signup.php");
    exit();
}

// Mobile number validation
if (!preg_match("/^7\d{8}$/", $mobile)) {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = "Invalid mobile number. Use format 7XXXXXXXX.";
    header("Location: pages-batchadmin-signup.php");
    exit();
}

// Check for duplicate regno or email
$check = $conn->prepare("SELECT id FROM admins WHERE regno = ? OR email = ?");
$check->bind_param("ss", $regno, $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = "Registration number or email already exists.";
    header("Location: pages-signup.php");
    exit();
}

// Insert the user
$stmt = $conn->prepare("INSERT INTO admins (name, regno, nic, email, gender, mobile, password)
                        VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $name, $regno, $nic, $email, $gender, $mobile, $password);

if ($stmt->execute()) {
    $_SESSION['status'] = 'success';
    $_SESSION['message'] = 'Account created successfully.';
    header("Location: pages-batchadmin-signup.php"); // Redirect to show success message
    exit();
} else {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Failed to create account.';
    header("Location: pages-batchadmin-signup.php");
    exit();
}

// Cleanup
$stmt->close();
$conn->close();
?>

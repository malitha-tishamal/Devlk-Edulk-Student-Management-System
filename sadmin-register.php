<?php
session_start();
require_once "includes/db-conn.php";

// Validate required POST fields
$requiredFields = ['username', 'nic', 'email', 'mobile', 'password'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = ucfirst($field) . " is required.";
        header("Location: pages-sadmin-signup.php");
        exit();
    }
}

// Sanitize inputs
$name      = trim($_POST['username']);
$nic       = strtoupper(trim($_POST['nic']));
$email     = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$mobile    = trim($_POST['mobile']);
$password  = password_hash($_POST['password'], PASSWORD_DEFAULT);

// NIC validation
if (!preg_match("/^(\d{9}[VXvx]|\d{12})$/", $nic)) {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = "Invalid NIC format.";
    header("Location: pages-sadmin-signup.php");
    exit();
}

// Mobile number validation
if (!preg_match("/^7\d{8}$/", $mobile)) {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = "Invalid mobile number. Use format 7XXXXXXXX.";
    header("Location: pages-sadmin-signup.php");
    exit();
}

// Check for duplicate email
$check = $conn->prepare("SELECT id FROM sadmins WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = "Email already exists.";
    header("Location: pages-sadmin-signup.php");
    exit();
}

// Insert into database
$stmt = $conn->prepare("INSERT INTO sadmins (name, nic, email, mobile, password) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $nic, $email, $mobile, $password);

if ($stmt->execute()) {
    $_SESSION['status'] = 'success';
    $_SESSION['message'] = 'Account created successfully.';
} else {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Failed to create account.';
}

// Cleanup
$stmt->close();
$conn->close();
header("Location: pages-sadmin-signup.php");
exit();
?>

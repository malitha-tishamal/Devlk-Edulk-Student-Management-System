<?php
session_start();
require_once "includes/db-conn.php";

// Utility: return JSON or set session + redirect
function respond($status, $message, $isAjax = false) {
    if ($isAjax) {
        echo json_encode(['status' => $status, 'message' => $message]);
    } else {
        $_SESSION['status'] = $status;
        $_SESSION['message'] = $message;
        header("Location: pages-signup.php");
    }
    exit();
}

// Check if request is AJAX
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Required fields
$requiredFields = ['username', 'regno', 'nic', 'email', 'gender', 'address', 'nowstatus', 'mobile', 'mobile2', 'password'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        respond('error', ucfirst($field) . " is required.", $isAjax);
    }
}

// Sanitize inputs
$name       = trim($_POST['username']);
$regno      = strtoupper(trim($_POST['regno']));
$nic        = strtoupper(trim($_POST['nic']));
$email      = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$gender     = $_POST['gender'];
$address    = trim($_POST['address']);
$nowstatus  = $_POST['nowstatus'];
$mobile     = trim($_POST['mobile']);     // personal
$mobile2    = trim($_POST['mobile2']);    // home
$password   = password_hash($_POST['password'], PASSWORD_DEFAULT);

// NIC format check (old and new formats)
if (!preg_match("/^(\d{9}[VXvx]|\d{12})$/", $nic)) {
    respond('error', "Invalid NIC format.", $isAjax);
}

// Mobile validation (+94 7XXXXXXXX)
//if (!preg_match("/^7\d{8}$/", $mobile) || !preg_match("/^7\d{8}$/", $mobile2)) {
   // respond('error', "Invalid mobile number(s). Use format 7XXXXXXXX.", $isAjax);
//}

// Check for existing regno or email
$check = $conn->prepare("SELECT id FROM students WHERE regno = ? OR email = ?");
$check->bind_param("ss", $regno, $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    respond('error', "Registration number or email already exists.", $isAjax);
}

// Insert user
$stmt = $conn->prepare("INSERT INTO students 
    (name, regno, nic, email, gender, address, nowstatus, mobile, mobile2, password)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("ssssssssss", 
    $name, $regno, $nic, $email, $gender, $address, $nowstatus, $mobile, $mobile2, $password);

if ($stmt->execute()) {
    respond('success', "Account created successfully.", $isAjax);
} else {
    respond('error', "Failed to create account. Please try again.", $isAjax);
}

// Cleanup
$stmt->close();
$conn->close();
?>

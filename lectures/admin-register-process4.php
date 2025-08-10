<?php
session_start();
require_once("../includes/db-conn.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize & assign variables
    $name       = trim($_POST['name']);
    $regno      = strtoupper(trim($_POST['regno']));
    $nic        = strtoupper(trim($_POST['nic']));
    $email      = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $mobile     = trim($_POST['mobile']);      // Personal
    $mobile2    = trim($_POST['mobile2']);     // Home
    $gender     = $_POST['gender'];
    $address    = trim($_POST['address']);
    $nowstatus  = $_POST['nowstatus'];
    $password   = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Basic validation
    if (!preg_match("/^(\d{9}[VXvx]|\d{12})$/", $nic)) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Invalid NIC format.';
        header("Location: pages-add-new-student.php");
        exit();
    }

    if (!preg_match("/^7\d{8}$/", $mobile) || !preg_match("/^7\d{8}$/", $mobile2)) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Mobile numbers must be in the format 7XXXXXXXX.';
        header("Location: pages-add-new-student.php");
        exit();
    }

    // Check for duplicates
    $checkQuery = "SELECT id FROM students WHERE email = ? OR nic = ? OR regno = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("sss", $email, $nic, $regno);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Email, NIC, or Registration Number already exists.';
        $stmt->close();
        header("Location: pages-add-new-student.php");
        exit();
    }
    $stmt->close();

    // Insert student
    $insertQuery = "INSERT INTO students (name, regno, nic, email, mobile, mobile2, gender, address, nowstatus, password)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ssssssssss", $name, $regno, $nic, $email, $mobile, $mobile2, $gender, $address, $nowstatus, $password);

    if ($stmt->execute()) {
        $_SESSION['status'] = 'success';
        $_SESSION['message'] = 'Student account successfully created!';
    } else {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Database error while inserting student.';
    }

    $stmt->close();
    header("Location: pages-add-new-student.php");
    exit();
}
?>

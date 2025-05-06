<?php
session_start();
require_once("../includes/db-conn.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize inputs
    $name = trim($_POST['name']);
    $regno = trim($_POST['regno']);
    $nic = strtoupper(trim($_POST['nic']));
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $gender = trim($_POST['gender']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Secure password

    // Check for duplicate email, NIC, or Reg No
    $checkQuery = "SELECT * FROM admins WHERE email = ? OR nic = ? OR regno = ?";
    if ($stmt = $conn->prepare($checkQuery)) {
        $stmt->bind_param("sss", $email, $nic, $regno);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['status'] = 'error';
            $_SESSION['message'] = 'Email, NIC, or Register Number already exists. Please try again with different details.';
            $stmt->close();
            header("Location: pages-add-batchadmin.php");
            exit();
        }
        $stmt->close();
    }

    // Insert admin into the database
    $insertQuery = "INSERT INTO admins (name, regno, nic, email, mobile, gender, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($insertQuery)) {
        $stmt->bind_param("sssssss", $name, $regno, $nic, $email, $mobile, $gender, $password);

        if ($stmt->execute()) {
            $_SESSION['status'] = 'success';
            $_SESSION['message'] = 'Admin account successfully created!';
        } else {
            $_SESSION['status'] = 'error';
            $_SESSION['message'] = 'Failed to create account. Please try again.';
        }

        $stmt->close();
        header("Location: pages-add-batchadmin.php");
        exit();
    } else {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Database error. Please try again.';
        header("Location: pages-add-batchadmin.php");
        exit();
    }
}
?>

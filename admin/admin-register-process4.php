<?php
session_start();
require_once("../includes/db-conn.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize form inputs
    $name = trim($_POST['name']);
    $regno = trim($_POST['regno']);
    $nic = trim($_POST['nic']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $gender = $_POST['gender'];
    $address = trim($_POST['address']);
    $nowstatus = $_POST['nowstatus'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Check for duplicate email, NIC or regno
    $checkQuery = "SELECT * FROM students WHERE email = ? OR nic = ? OR regno = ?";
    if ($stmt = $conn->prepare($checkQuery)) {
        $stmt->bind_param("sss", $email, $nic, $regno);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['status'] = 'error';
            $_SESSION['message'] = 'Email, NIC, or Register Number already exists.';
            header("Location: pages-add-new-student.php");
            $stmt->close();
            exit();
        }
        $stmt->close();
    }

    // Insert student into database
    $insertQuery = "INSERT INTO students (name, regno, nic, email, mobile, gender, address, nowstatus, password)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($insertQuery)) {
        $stmt->bind_param("sssssssss", $name, $regno, $nic, $email, $mobile, $gender, $address, $nowstatus, $password);

        if ($stmt->execute()) {
            $_SESSION['status'] = 'success';
            $_SESSION['message'] = 'Student account successfully created!';
            header("Location: pages-add-new-student.php");
            exit();
        } else {
            $_SESSION['status'] = 'error';
            $_SESSION['message'] = 'Database error while inserting student.';
            header("Location: pages-add-new-student.php");
            exit();
        }

        $stmt->close();
    } else {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Database query preparation failed.';
        header("Location: pages-add-new-student.php");
        exit();
    }
}
?>

<?php
session_start();
require_once 'includes/db-conn.php';

// Set timezone to Sri Lanka
date_default_timezone_set('Asia/Colombo');
$conn->query("SET time_zone = '+05:30'");

// Initialize login attempts
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['lockout_stage'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

// Lockout durations (in seconds)
$lockout_durations = [5 * 60, 10 * 60, 20 * 60, 60 * 60];

// Check for lockout
if ($_SESSION['login_attempts'] >= 3) {
    $stage = $_SESSION['lockout_stage'];
    $timeout = $lockout_durations[$stage] ?? end($lockout_durations);
    $remaining = ($_SESSION['last_attempt_time'] + $timeout) - time();

    if ($remaining > 0) {
        $_SESSION['error_message'] = "Too many failed attempts. Try again in " . ceil($remaining / 60) . " minute(s).";
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['lockout_stage'] += 1;
    }
}

if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $current_time = date("Y-m-d H:i:s");

    // Tables to check
    $tables = ['sadmins', 'admins', 'students', 'lectures'];

    foreach ($tables as $table) {
        $sql = "SELECT * FROM $table WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                // Check account status
                if ($user['status'] !== 'approved') {
                    $_SESSION['error_message'] = "Your account is currently '{$user['status']}'. Please contact support.";
                    header("Location: index.php");
                    exit();
                }

                // Reset login attempts
                $_SESSION['login_attempts'] = 0;
                $_SESSION['lockout_stage'] = 0;

                // Role-based session setup and redirect
                if ($table === 'sadmins') {
                    $_SESSION['sadmin_id'] = $user['id'];
                    $_SESSION['success_message'] = "Welcome Super Admin!";
                    $redirect = "sadmin/user-profile.php";

                } elseif ($table === 'admins') {
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['success_message'] = "Welcome Admin!";
                    $redirect = "admin/user-profile.php";

                } elseif ($table === 'students') {
                    $_SESSION['student_id'] = $user['id'];
                    $_SESSION['success_message'] = "Welcome Student!";
                    $redirect = "user-profile.php";

                } elseif ($table === 'lectures') {
                    $_SESSION['lecture_id'] = $user['id'];
                    $_SESSION['success_message'] = "Welcome Lecturer!";
                    $redirect = "lectures/user-profile.php";
                }

                // Update last login timestamp
                $update = $conn->prepare("UPDATE $table SET last_login = ? WHERE id = ?");
                $update->bind_param("si", $current_time, $user['id']);
                $update->execute();

                header("Location: $redirect");
                exit();
            }
        }
    }

    // Login failed
    $_SESSION['login_attempts'] += 1;
    $_SESSION['last_attempt_time'] = time();
    $_SESSION['error_message'] = "Invalid email or password.";

    if ($_SESSION['login_attempts'] % 3 == 0) {
        $_SESSION['lockout_stage'] += 1;
    }

    header("Location: index.php");
    exit();
}
?>

<?php
session_start();
require_once 'includes/db-conn.php';
date_default_timezone_set('Asia/Colombo');
$conn->query("SET time_zone = '+05:30'");

// Login attempts & lockout
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['lockout_stage'] = 0;
    $_SESSION['last_attempt_time'] = time();
}
$lockout_durations = [5*60, 10*60, 20*60, 60*60];
if ($_SESSION['login_attempts'] >= 3) {
    $stage = $_SESSION['lockout_stage'];
    $timeout = $lockout_durations[$stage] ?? end($lockout_durations);
    $remaining = ($_SESSION['last_attempt_time'] + $timeout) - time();
    if ($remaining > 0) {
        $_SESSION['error_message'] = "Too many failed attempts. Try again in " . ceil($remaining / 60) . " minute(s).";
        header("Location: index.php"); exit();
    } else {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['lockout_stage'] += 1;
    }
}

// Handle login
if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $current_time = date("Y-m-d H:i:s");
    $tables = ['sadmins','admins','students','lectures'];

    foreach ($tables as $table) {
        $sql = "SELECT * FROM $table WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s",$email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'approved') {
                    $_SESSION['error_message'] = "Your account is currently '{$user['status']}'. Please contact support.";
                    header("Location: index.php"); exit();
                }

                // Reset login attempts
                $_SESSION['login_attempts'] = 0; $_SESSION['lockout_stage'] = 0;

                // Set session & user type
                if ($table==='students') {
                    $_SESSION['student_id'] = $user['id'];
                    $_SESSION['student_name'] = $user['name'];
                    $_SESSION['student_regno'] = $user['regno'];
                    $_SESSION['user_type'] = 'student';
                    $_SESSION['user_id'] = $user['id'];
                    $redirect = "user-profile.php";
                } elseif ($table==='sadmins') {
                    $_SESSION['sadmin_id']=$user['id'];
                    $_SESSION['user_type'] = 'sadmin';
                    $_SESSION['user_id'] = $user['id'];
                    $redirect="sadmin/user-profile.php";
                } elseif ($table==='admins') {
                    $_SESSION['admin_id']=$user['id'];
                    $_SESSION['user_type'] = 'admin';
                    $_SESSION['user_id'] = $user['id'];
                    $redirect="admin/user-profile.php";
                } elseif ($table==='lectures') {
                    $_SESSION['lecture_id']=$user['id'];
                    $_SESSION['user_type'] = 'lecture';
                    $_SESSION['user_id'] = $user['id'];
                    $redirect="lectures/user-profile.php";
                }

                // Update last login
                $update = $conn->prepare("UPDATE $table SET last_login=? WHERE id=?");
                $update->bind_param("si",$current_time,$user['id']); $update->execute();

                // Server-side info for initial log
                $ip_address = $_SERVER['REMOTE_ADDR'] === '::1' ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                $session_id = session_id();
                $current_url = $_SERVER['REQUEST_URI'];
                $referrer_url = $_SERVER['HTTP_REFERER'] ?? '';

                // Insert initial log (latitude/longitude etc. will update later)
                $log_table = [
                    'student'=>'students_logs',
                    'admin'=>'admin_logs',
                    'sadmin'=>'sadmin_logs',
                    'lecture'=>'lectures_logs'
                ][$table==='students'?'student':$table];

                $stmt_log = $conn->prepare("INSERT INTO $log_table 
                    (student_id, student_name, regno, ip_address, user_agent, session_id, login_time, current_url, referrer_url)
                    VALUES (?,?,?,?,?,?,?,?,?)");

                $regno = $user['regno'] ?? NULL;
                $stmt_log->bind_param("issssssss",
                    $user['id'],
                    $user['name'],
                    $regno,
                    $ip_address,
                    $user_agent,
                    $session_id,
                    $current_time,
                    $current_url,
                    $referrer_url
                );
                $stmt_log->execute();

                header("Location: $redirect"); exit();
            }
        }
    }

    // Login failed
    $_SESSION['login_attempts'] += 1;
    $_SESSION['last_attempt_time'] = time();
    $_SESSION['error_message'] = "Invalid email or password.";
    if ($_SESSION['login_attempts'] %3==0) { $_SESSION['lockout_stage'] +=1; }
    header("Location: index.php"); exit();
}
?>

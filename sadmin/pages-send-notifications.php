<?php
session_start();
require_once '../includes/db-conn.php';

// Identify sender
$sender_id = null;
$sender_role = null;

if (isset($_SESSION['sadmin_id'])) {
    $sender_id = $_SESSION['sadmin_id'];
    $sender_role = "sadmin";
} elseif (isset($_SESSION['admin_id'])) {
    $sender_id = $_SESSION['admin_id'];
    $sender_role = "admin";
} elseif (isset($_SESSION['lecture_id'])) {
    $sender_id = $_SESSION['lecture_id'];
    $sender_role = "lecture";
} elseif (isset($_SESSION['student_id'])) {
    $sender_id = $_SESSION['student_id'];
    $sender_role = "student";
} else {
    die("Not logged in!");
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_role = $_POST['receiver_role'];
    $message = trim($_POST['message']);

    if (!empty($message)) {
        $sql = "INSERT INTO notifications (sender_id, sender_role, receiver_role, message) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $sender_id, $sender_role, $receiver_role, $message);
        $stmt->execute();
        $success = "Notification sent successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send Notification</title>
</head>
<body>
    <h2>Send Notification</h2>
    <?php if (!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>

    <form method="post">
        <label>Send To:</label>
        <select name="receiver_role" required>
            <option value="all">All</option>
            <option value="sadmin">Super Admin</option>
            <option value="admin">Admin</option>
            <option value="lecture">Lecturers</option>
            <option value="student">Students</option>
        </select>
        <br><br>
        <textarea name="message" placeholder="Type your message..." required></textarea><br><br>
        <button type="submit">Send</button>
    </form>
</body>
</html>

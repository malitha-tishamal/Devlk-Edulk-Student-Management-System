<?php
session_start();
require_once '../includes/db-conn.php';

// Identify logged user role
$user_role = null;

if (isset($_SESSION['sadmin_id'])) {
    $user_role = "sadmin";
} elseif (isset($_SESSION['admin_id'])) {
    $user_role = "admin";
} elseif (isset($_SESSION['lecture_id'])) {
    $user_role = "lecture";
} elseif (isset($_SESSION['student_id'])) {
    $user_role = "student";
} else {
    die("Not logged in!");
}

// Fetch notifications
$sql = "SELECT * FROM notifications 
        WHERE receiver_role = ? OR receiver_role = 'all'
        ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_role);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Notifications</title>
</head>
<body>
    <h2>Your Notifications</h2>
    <?php while ($row = $result->fetch_assoc()) { ?>
        <div style="border:1px solid #ddd; padding:10px; margin:10px;">
            <strong>From:</strong> <?php echo ucfirst($row['sender_role']); ?><br>
            <strong>Message:</strong> <?php echo htmlspecialchars($row['message']); ?><br>
            <small><?php echo $row['created_at']; ?></small>
        </div>
    <?php } ?>
</body>
</html>

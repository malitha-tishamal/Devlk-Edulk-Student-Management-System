<?php
session_start();
require_once 'includes/db-conn.php';

$session_id = session_id();
$data = json_decode(file_get_contents('php://input'), true);

if($data && isset($_SESSION['user_type']) && isset($_SESSION['user_id'])) {
    $user_type = $_SESSION['user_type'];
    $user_id = $_SESSION['user_id'];

    // Map user type to table
    $table_map = [
        'student'=>'students_logs',
        'admin'=>'admin_logs',
        'sadmin'=>'sadmin_logs',
        'lecture'=>'lectures_logs'
    ];

    if(!isset($table_map[$user_type])) exit;
    $table = $table_map[$user_type];

    $columns = "device_type=?, device_vendor=?, device_model=?, os=?, browser=?, browser_version=?,
                language=?, screen_resolution=?, timezone=?, online_status=?, battery_level=?,
                orientation=?, touch_support=?, pixel_ratio=?, connection_type=?, viewport_size=?,
                latitude=?, longitude=?";

    $stmt = $conn->prepare("UPDATE $table SET $columns WHERE session_id=?");
    $stmt->bind_param(
        "sssssssssssssssssss",
        $data['device_type'],
        $data['device_vendor'],
        $data['device_model'],
        $data['os'],
        $data['browser'],
        $data['browser_version'],
        $data['language'],
        $data['screen_resolution'],
        $data['timezone'],
        $data['online_status'],
        $data['battery_level'],
        $data['orientation'],
        $data['touch_support'],
        $data['pixel_ratio'],
        $data['connection_type'],
        $data['viewport_size'],
        $data['latitude'],
        $data['longitude'],
        $session_id
    );
    $stmt->execute();
}
?>

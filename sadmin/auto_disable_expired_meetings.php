<?php
require_once '../includes/db-conn.php';

date_default_timezone_set('Asia/Colombo'); // Ensure correct timezone
$now = new DateTime();

$sql = "SELECT * FROM meetings WHERE status = 'active'";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $start = DateTime::createFromFormat('Y-m-d H:i:s', $row['date'] . ' ' . $row['start_time']);
    if (!$start) continue;

    $expiry = $row['link_expiry_status'];
    $expireTime = clone $start;

    switch ($expiry) {
        case '1h':  $expireTime->modify('+1 hour'); break;
        case '2h':  $expireTime->modify('+2 hours'); break;
        case '4h':  $expireTime->modify('+4 hours'); break;
        case '6h':  $expireTime->modify('+6 hours'); break;
        case '12h': $expireTime->modify('+12 hours'); break;
        case '24h': $expireTime->modify('+24 hours'); break;
        case '2d':  $expireTime->modify('+2 days'); break;
        case '4d':  $expireTime->modify('+4 days'); break;
        case '7d':  $expireTime->modify('+7 days'); break;
        case '1m':  $expireTime->modify('+1 month'); break;
        case 'permanent': continue 2; // correctly skip current meeting and continue while loop
        default: continue 2; // unknown expiry -> skip this meeting
    }

    if ($now > $expireTime) {
        $updateStmt = $conn->prepare("UPDATE meetings SET status = 'disabled' WHERE id = ?");
        $updateStmt->bind_param("i", $row['id']);
        $updateStmt->execute();
        $updateStmt->close();
    }
}
?>

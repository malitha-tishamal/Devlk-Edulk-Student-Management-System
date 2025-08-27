<?php
session_start();
require_once '../includes/db-conn.php';

// Check admin login
if (!isset($_SESSION['sadmin_id'])) {
    header("Location: index.php");
    exit();
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=recording_plays_' . date('Y-m-d') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'Student Name',
    'Registration Number',
    'Email',
    'Semester',
    'Subject',
    'Recording Title',
    'Remaining Play Count',
    'Plays Left',
    'Last Played'
]);

// --- Apply the same filters as in the main page ---
$where = [];
$params = [];
$types  = "";

// Semester filter
if (!empty($_REQUEST['semester'])) {
    $where[] = "sub.semester = ?";
    $params[] = $_REQUEST['semester'];
    $types   .= "s";
}

// Subject filter
if (!empty($_REQUEST['subject_id'])) {
    $where[] = "r.subject_id = ?";
    $params[] = $_REQUEST['subject_id'];
    $types   .= "i";
}

// Student filter (by name or regno)
if (!empty($_REQUEST['student'])) {
    $where[] = "(s.name LIKE ? OR s.regno LIKE ?)";
    $params[] = "%" . $_REQUEST['student'] . "%";
    $params[] = "%" . $_REQUEST['student'] . "%";
    $types   .= "ss";
}

// Batch Year filter (first 2 digits of regno year)
if (!empty($_REQUEST['batch_year'])) {
    $where[] = "SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(s.regno, '/', 3), '/', -1), 1, 2) = ?";
    $params[] = $_REQUEST['batch_year'];
    $types .= "s";
}

// Build WHERE clause
$where_sql = "";
if (count($where) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where);
}

// Main query (same as in the main page)
$sql = "SELECT 
            s.id AS student_id,
            s.name AS student_name,
            s.regno,
            s.email,
            sub.semester,
            sub.name AS subject_name,
            r.title AS recording_title,
            rsp.remaining_play_count,
            rsp.plays_left,
            rsp.last_played
        FROM recording_student_plays rsp
        LEFT JOIN students s ON rsp.student_id = s.id
        LEFT JOIN recordings r ON rsp.recording_id = r.id
        LEFT JOIN subjects sub ON r.subject_id = sub.id
        $where_sql
        ORDER BY rsp.last_played DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Output data as CSV rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['student_name'],
        $row['regno'],
        $row['email'],
        $row['semester'],
        $row['subject_name'],
        $row['recording_title'],
        $row['remaining_play_count'],
        $row['plays_left'],
        $row['last_played'] ? date('M j, Y g:i A', strtotime($row['last_played'])) : 'Never'
    ]);
}

// Close connections
$stmt->close();
$conn->close();
fclose($output);
exit();
?>
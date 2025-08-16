<?php
session_start();
require_once 'includes/db-conn.php';
header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['error' => '⛔ You must log in to watch this video.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$recordingId = intval($data['id']);
$studentId   = intval($_SESSION['student_id']);

if (!$recordingId) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

// Get recording info
$stmt = $conn->prepare("SELECT play_count, view_limit_minutes FROM recordings WHERE id = ?");
$stmt->bind_param("i", $recordingId);
$stmt->execute();
$rec = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$rec) {
    echo json_encode(['error' => 'Recording not found']);
    exit;
}

// Get student-specific play record
$stmt = $conn->prepare("SELECT plays_left FROM recording_student_plays WHERE recording_id = ? AND student_id = ?");
$stmt->bind_param("ii", $recordingId, $studentId);
$stmt->execute();
$studentPlay = $stmt->get_result()->fetch_assoc();
$stmt->close();

$plays_left = $studentPlay['plays_left'] ?? $rec['view_limit_minutes'];

// Check if limit reached
if ($plays_left <= 0) {
    echo json_encode(['error' => 'Play limit reached']);
    exit;
}

// Decrement student plays_left
if ($studentPlay) {
    $stmt = $conn->prepare("UPDATE recording_student_plays SET plays_left = plays_left - 1, last_played = NOW() WHERE recording_id = ? AND student_id = ?");
    $stmt->bind_param("ii", $recordingId, $studentId);
    $stmt->execute();
    $stmt->close();
} else {
    $stmt = $conn->prepare("INSERT INTO recording_student_plays (recording_id, student_id, plays_left, last_played) VALUES (?, ?, ?, NOW())");
    $initialPlays = $rec['view_limit_minutes'] - 1;
    $stmt->bind_param("iii", $recordingId, $studentId, $initialPlays);
    $stmt->execute();
    $stmt->close();
    $plays_left = $initialPlays;
}

// Update global play count
$stmt = $conn->prepare("UPDATE recordings SET play_count = play_count + 1 WHERE id = ?");
$stmt->bind_param("i", $recordingId);
$stmt->execute();
$stmt->close();

echo json_encode([
    'success' => true,
    'global_count' => $rec['play_count'] + 1,
    'plays_left' => $plays_left
]);
?>

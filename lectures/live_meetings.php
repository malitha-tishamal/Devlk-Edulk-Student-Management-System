<?php
session_start();
require_once '../includes/db-conn.php';

// Check admin authentication
if (!isset($_SESSION['lecture_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// Get live meetings
if ($action === 'get_live_meetings') {
    $stmt = $conn->prepare("
        SELECT m.id, m.title, m.zoom_link, m.date, m.start_time, 
               s.name AS created_by, m.subject,m.role,m.created_by,m.link_expiry_status 
        FROM meetings m
        LEFT JOIN sadmins s ON m.created_by = s.id
        WHERE m.is_live = TRUE
        ORDER BY m.date DESC, m.start_time DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $meetings = [];
    while ($row = $result->fetch_assoc()) {
        $meetings[] = $row;
    }
    echo json_encode($meetings);
    exit();
}

// Start a meeting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'start_meeting') {
    $meeting_id = intval($_POST['meeting_id'] ?? 0);
    
    // Verify meeting exists and isn't already live
    $stmt = $conn->prepare("SELECT id, status FROM meetings WHERE id = ?");
    $stmt->bind_param("i", $meeting_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $meeting = $result->fetch_assoc();
    
    if (!$meeting) {
        echo json_encode(['success' => false, 'message' => 'Meeting not found']);
        exit();
    }
    
    if ($meeting['status'] !== 'active') {
        echo json_encode(['success' => false, 'message' => 'Meeting is not active']);
        exit();
    }
    
    // Set meeting as live
    $stmt = $conn->prepare("UPDATE meetings SET is_live = TRUE WHERE id = ?");
    $stmt->bind_param("i", $meeting_id);
    $success = $stmt->execute();
    
    echo json_encode(['success' => $success, 'redirect' => $_POST['zoom_link'] ?? '']);
    exit();
}

// End a meeting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'end_meeting') {
    $meeting_id = intval($_POST['meeting_id'] ?? 0);
    
    // Verify meeting exists and is live
    $stmt = $conn->prepare("SELECT id FROM meetings WHERE id = ? AND is_live = TRUE");
    $stmt->bind_param("i", $meeting_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Meeting not found or not live']);
        exit();
    }
    
    // Set meeting as not live
    $stmt = $conn->prepare("UPDATE meetings SET is_live = FALSE WHERE id = ?");
    $stmt->bind_param("i", $meeting_id);
    $success = $stmt->execute();
    
    echo json_encode(['success' => $success]);
    exit();
}

// Invalid request
header("HTTP/1.1 400 Bad Request");
echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>
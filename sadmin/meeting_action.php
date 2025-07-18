<?php
session_start();
require_once '../includes/db-conn.php';

// Validate admin session if needed

$id = $_POST['id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$id || !$action) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$id = intval($id);

switch ($action) {
    case 'activate':
        $stmt = $conn->prepare("UPDATE meetings SET status = 'active' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $success = $stmt->affected_rows > 0;
        $stmt->close();
        break;

    case 'disable':
        $stmt = $conn->prepare("UPDATE meetings SET status = 'disabled' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $success = $stmt->affected_rows > 0;
        $stmt->close();
        break;

    case 'delete':
        $stmt = $conn->prepare("DELETE FROM meetings WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $success = $stmt->affected_rows > 0;
        $stmt->close();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
        exit;
}

echo json_encode(['success' => $success]);
$conn->close();
?>

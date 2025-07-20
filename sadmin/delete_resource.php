<?php
session_start();
require_once '../includes/db-conn.php';

// Check if user is logged in as super admin
if (!isset($_SESSION['sadmin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resource_id = intval($_POST['id'] ?? 0);
    if ($resource_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid resource ID']);
        exit();
    }

    // Get resource info to delete file if it's a file
    $stmt = $conn->prepare("SELECT resource_type, resource_data FROM meeting_resources WHERE id = ?");
    $stmt->bind_param("i", $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Resource not found']);
        exit();
    }

    $resource = $result->fetch_assoc();
    $stmt->close();

    // Delete physical file if resource_type is 'file'
    if ($resource['resource_type'] === 'file') {
        $filePath = __DIR__ . '/../uploads/meeting_resources/' . basename($resource['resource_data']);
        if (file_exists($filePath)) {
            @unlink($filePath); // suppress errors
        }
    }

    // Delete resource record from database
    $stmt = $conn->prepare("DELETE FROM meeting_resources WHERE id = ?");
    $stmt->bind_param("i", $resource_id);
    $success = $stmt->execute();
    $stmt->close();

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete resource']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

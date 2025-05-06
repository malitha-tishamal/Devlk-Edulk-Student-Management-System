<?php
session_start(); // Start session to store messages

// Set the default timezone to Sri Lanka Standard Time (SLST)
date_default_timezone_set('Asia/Colombo');

// Include the database connection
require_once "includes/db-conn.php";

// Enable detailed error reporting (for debugging)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate form inputs
    $antibioticname = isset($_POST['antibiotic_name']) ? trim($_POST['antibiotic_name']) : null;
    $dosage = isset($_POST['dosage']) && trim($_POST['dosage']) !== '' ? trim($_POST['dosage']) : null;
    $itemCount = isset($_POST['item_count']) ? intval($_POST['item_count']) : null;
    $ward = isset($_POST['ward']) ? trim($_POST['ward']) : null;
    // Use selected date or current timestamp (based on the timezone set)
    $releaseTime = isset($_POST['date']) && !empty($_POST['date']) ? $_POST['date'] : date('Y-m-d H:i:s'); 
    $type = isset($_POST['type']) ? trim($_POST['type']) : null;
    $ant_type = isset($_POST['ant_type']) ? trim($_POST['ant_type']) : null;

    // Validate required fields
    if (empty($antibioticname) || empty($itemCount) || empty($ward) || empty($type) || empty($ant_type)) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = "Error: Missing required fields!";
        header("Location: pages-release-antibiotic-odd.php");
        exit();
    }

    // Ensure that item count is a valid number
    if ($itemCount <= 0) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = "Error: Invalid item count!";
        header("Location: pages-release-antibiotic-odd.php");
        exit();
    }

    // Prepare the SQL query
    $query = "INSERT INTO releases (antibiotic_name, dosage, item_count, release_time, ward_name, type, ant_type) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($query)) {
        // Bind parameters correctly
        $stmt->bind_param("ssissss", $antibioticname, $dosage, $itemCount, $releaseTime, $ward, $type, $ant_type);

        // Execute the query
        if ($stmt->execute()) {
            $_SESSION['status'] = 'success';
            $_SESSION['message'] = "Antibiotic release inserted successfully!";
        } else {
            $_SESSION['status'] = 'error';
            $_SESSION['message'] = "Error inserting antibiotic release: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = "Error: Failed to prepare SQL query!";
    }

    // Close the database connection
    $conn->close();

    // Redirect back to the same page
    header("Location: pages-release-antibiotic-odd.php");
    exit();
}
?>

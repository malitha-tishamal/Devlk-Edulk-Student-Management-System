<?php
session_start(); // Start session for messages
require_once "../includes/db-conn.php";  // Include the database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $antibiotic_name = trim($_POST['antibiotic_name']);
    $dosages = $_POST['dosage'];  // Array of dosages

    // Validate input
    if (empty($antibiotic_name) || empty($dosages)) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Please fill in all fields.';
        header("Location: pages-add-antibiotic.php");
        exit();
    }

    // Check if the antibiotic already exists
    $check_sql = "SELECT id FROM antibiotics WHERE name = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $antibiotic_name);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Antibiotic already exists!';
        header("Location: pages-add-antibiotic.php");
        exit();
    }
    $check_stmt->close();

    // Insert new antibiotic name
    $sql = "INSERT INTO antibiotics (name) VALUES (?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $antibiotic_name);
        if ($stmt->execute()) {
            $antibiotic_id = $stmt->insert_id;  // Get the last inserted ID

            // Prepare dosage insert statement
            $dosage_sql = "INSERT INTO dosages (antibiotic_id, dosage) VALUES (?, ?)";
            $dosage_stmt = $conn->prepare($dosage_sql);

            foreach ($dosages as $dosage) {
                $dosage_stmt->bind_param("is", $antibiotic_id, $dosage);
                $dosage_stmt->execute();
            }

            $_SESSION['status'] = 'success';
            $_SESSION['message'] = 'Antibiotic and dosages added successfully!';
            header("Location: pages-add-antibiotic.php");
            exit();
        } else {
            $_SESSION['status'] = 'error';
            $_SESSION['message'] = 'Error: Could not insert antibiotic.';
        }
        $stmt->close();
    } else {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Database error: ' . $conn->error;
    }

    header("Location: pages-add-antibiotic.php");
    exit();
}
?>

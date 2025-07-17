<?php
session_start(); 
include '../includes/db-conn.php';

// Get the form data
$lecturer_id = $_POST['lecturer_id'];
$subject_ids = $_POST['subject_ids'];  

if (!empty($lecturer_id) && !empty($subject_ids)) {

    // Check for existing assignments
    $check_query = "SELECT * FROM lectures_assignment WHERE lecturer_id = ? AND subject_id = ?";
    $check_stmt = $conn->prepare($check_query);

    // Prepare the insert query for new assignments
    $query = "INSERT INTO lectures_assignment (lecturer_id, subject_id) VALUES (?, ?)";
    $stmt = $conn->prepare($query);

    $success = true;
    foreach ($subject_ids as $subject_id) {

        // Check if the subject is already assigned to the lecturer
        $check_stmt->bind_param("ii", $lecturer_id, $subject_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $_SESSION['status'] = 'error';
            $_SESSION['message'] = "Subject with ID " . $subject_id . " is already assigned to this lecturer. Skipping.";
            $success = false;
        } else {
            // Insert the new subject assignment
            $stmt->bind_param("ii", $lecturer_id, $subject_id);
            if (!$stmt->execute()) {
                $_SESSION['status'] = 'error';
                $_SESSION['message'] = "Error: Could not assign subject with ID " . $subject_id;
                $success = false;
                break; 
            }
        }
    }

    if ($success) {
        $_SESSION['status'] = 'success';
        $_SESSION['message'] = "Subjects successfully assigned to the lecturer.";
    }

    // Close the prepared statements
    $check_stmt->close();
    $stmt->close();
} else {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = "Please select a lecturer and at least one subject.";
}

$conn->close();

// Redirect back to the page with the message
header("Location: pages-assign-subject.php");
exit();
?>

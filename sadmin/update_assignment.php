<?php
include '../includes/db-conn.php';

// Get the form data
$assignment_id = $_POST['assignment_id'];
$subject_id = $_POST['subject_id'];

// Update the subject for the given lecturer assignment
$query = "UPDATE lectures_assignment SET subject_id = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $subject_id, $assignment_id);

if ($stmt->execute()) {
    echo "Subject assignment updated successfully.";
     header("Location: pages-assign-subjects.php"); 
} else {
    echo "Error updating subject assignment.";
     header("Location: pages-assign-subjects.php"); 
}

$stmt->close();
$conn->close();
?>

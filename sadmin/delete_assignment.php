<?php
include '../includes/db-conn.php';

// Check if the assignment_id is set in the URL
if (isset($_GET['assignment_id'])) {
    $assignment_id = $_GET['assignment_id'];

    // Prepare the DELETE query to remove the assignment from lectures_assignment table
    $query = "DELETE FROM lectures_assignment WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $assignment_id);

    // Execute the query and check if the deletion was successful
    if ($stmt->execute()) {
        echo "Assignment deleted successfully!";
        // Redirect to the list page (or back to the previous page)
        header("Location: pages-assignments-manage.php"); 
        exit();
    } else {
        echo "Error deleting assignment: " . $conn->error;
    }

    // Close the prepared statement
    $stmt->close();
} else {
    echo "No assignment ID provided.";
}

// Close the database connection
$conn->close();
?>

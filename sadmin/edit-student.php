<?php
session_start();
require_once '../includes/db-conn.php';

// Redirect if not logged in
if (!isset($_SESSION['sadmin_id'])) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid request.";
    header("Location: manage-students.php");
    exit();
}

$student_id = $_GET['id'];

// Fetch admin details
$user_id = $_SESSION['sadmin_id'];
$sql = "SELECT name, email, nic,mobile,profile_picture FROM sadmins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch student details
$sql = "SELECT * FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    $_SESSION['error_message'] = "Student not found.";
    header("Location: manage-students.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['name']);
    $email = trim($_POST['email']);
    $nic = trim($_POST['nic']);
    $mobile = trim($_POST['mobile']);
    $study_year = trim($_POST['study_year']);

    if (empty($name) || empty($email) || empty($nic) || empty($mobile) || empty($study_year)) {
        $_SESSION['error_message'] = "All fields are required!";
    } else {
        $sql = "UPDATE students SET name=?, email=?, nic=?, mobile=?, study_year=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $name, $email, $nic, $mobile, $study_year, $student_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Student details updated successfully!";
            header("Location: manage-students.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error updating student details.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Edulk</title>
    <?php include_once("../includes/css-links-inc.php"); ?>
</head>
<body>
    <?php include_once("../includes/header.php"); ?>
    <?php include_once("../includes/sadmin-sidebar.php"); ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Edit Student</h1>
        </div>
        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Edit Student Details</h5>
                            <?php if (isset($_SESSION['error_message'])): ?>
                                <div class='alert alert-danger'><?= $_SESSION['error_message']; ?></div>
                                <?php unset($_SESSION['error_message']); ?>
                            <?php endif; ?>
                            <?php if (isset($_SESSION['success_message'])): ?>
                                <div class='alert alert-success'><?= $_SESSION['success_message']; ?></div>
                                <?php unset($_SESSION['success_message']); ?>
                            <?php endif; ?>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($student['name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($student['email']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="nic" class="form-label">NIC</label>
                                    <input type="text" class="form-control" id="nic" name="nic" value="<?= htmlspecialchars($student['nic']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="mobile" class="form-label">Mobile</label>
                                    <input type="text" class="form-control" id="mobile" name="mobile" value="<?= htmlspecialchars($student['mobile']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="study_year" class="form-label">Study Year</label>
                                    <input type="text" class="form-control" id="study_year" name="study_year" value="<?= htmlspecialchars($student['study_year']); ?>" required>
                                </div>
                                <button type="submit" class="btn btn-success">Update</button>
                                <a href="manage-students.php" class="btn btn-danger">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <?php include_once("../includes/footer.php"); ?>
    <?php include_once("../includes/js-links-inc.php"); ?>
</body>
</html>

<?php $conn->close(); ?>

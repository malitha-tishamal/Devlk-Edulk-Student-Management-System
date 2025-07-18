<?php
session_start();
require_once '../includes/db-conn.php';

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
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $nic = trim($_POST['nic']);
    $mobile = trim($_POST['mobile']);
    $mobile2 = trim($_POST['mobile2']);
    $gender = $_POST['gender'];
    $address = trim($_POST['address']);
    $nowstatus = trim($_POST['nowstatus']);
    $status = $_POST['status'];

    if ($name && $email && $nic && $mobile && $gender && $address && $status) {
        $updateSql = "UPDATE students SET name=?, email=?, nic=?, mobile=?, mobile2=?, gender=?, address=?, nowstatus=?, status=? WHERE id=?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("sssssssssi", $name, $email, $nic, $mobile, $mobile2, $gender, $address, $nowstatus, $status, $student_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Student updated successfully.";
            header("Location: manage-students.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Failed to update student.";
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Update Student Details</h5>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($student['name']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($student['email']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">NIC</label>
                        <input type="text" name="nic" class="form-control" required value="<?= htmlspecialchars($student['nic']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mobile</label>
                        <input type="text" name="mobile" class="form-control" required value="<?= htmlspecialchars($student['mobile']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Home / Other Number</label>
                        <input type="text" name="mobile2" class="form-control" value="<?= htmlspecialchars($student['mobile2']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select" required>
                            <option value="Male" <?= ($student['gender'] == 'Male') ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($student['gender'] == 'Female') ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" required><?= htmlspecialchars($student['address']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Now Status</label>
                        <input type="text" name="nowstatus" class="form-control" value="<?= htmlspecialchars($student['nowstatus']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Account Status</label>
                        <select name="status" class="form-select" required>
                            <option value="active" <?= ($student['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="pending" <?= ($student['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                            <option value="disabled" <?= ($student['status'] == 'disabled') ? 'selected' : '' ?>>Disabled</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">Update</button>
                    <a href="manage-students.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once("../includes/footer.php"); ?>
<?php include_once("../includes/js-links-inc.php"); ?>
</body>
</html>

<?php $conn->close(); ?>

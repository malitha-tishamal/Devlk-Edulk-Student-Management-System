<?php
session_start();
require_once '../includes/db-conn.php';

if (!isset($_SESSION['sadmin_id'])) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid request.";
    header("Location: manage-batchadmins.php");
    exit();
}

$admin_id = $_GET['id'];

// Fetch sadmin info
$sadmin_id = $_SESSION['sadmin_id'];
$stmt = $conn->prepare("SELECT name, email, nic, mobile, profile_picture FROM sadmins WHERE id = ?");
$stmt->bind_param("i", $sadmin_id);
$stmt->execute();
$sadmin_result = $stmt->get_result();
$user = $sadmin_result->fetch_assoc();
$stmt->close();

// Fetch admin (representer) info
$stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

if (!$admin) {
    $_SESSION['error_message'] = "Representer not found.";
    header("Location: manage-batchadmins.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $nic = trim($_POST['nic']);
    $mobile = trim($_POST['mobile']);
    $registration_number = trim($_POST['registration_number']);
    $gender = $_POST['gender'];
    $status = $_POST['status'];

    if (empty($name) || empty($email) || empty($nic) || empty($mobile) || empty($gender) || empty($status)) {
        $_SESSION['error_message'] = "All fields are required!";
    } else {
        $stmt = $conn->prepare("UPDATE admins SET name=?, email=?, nic=?, mobile=?, registration_number=?, gender=?, status=? WHERE id=?");
        $stmt->bind_param("sssssssi", $name, $email, $nic, $mobile, $registration_number, $gender, $status, $admin_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Representer updated successfully!";
            header("Location: manage-batchadmins.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error updating details.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Representer</title>
    <?php include_once("../includes/css-links-inc.php"); ?>
</head>
<body>

<?php include_once("../includes/header.php"); ?>
<?php include_once("../includes/sadmin-sidebar.php"); ?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Edit Representer</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Edit Representer</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body pt-4">

                <?php
                if (isset($_SESSION['error_message'])) {
                    echo "<div class='alert alert-danger'>" . $_SESSION['error_message'] . "</div>";
                    unset($_SESSION['error_message']);
                }

                if (isset($_SESSION['success_message'])) {
                    echo "<div class='alert alert-success'>" . $_SESSION['success_message'] . "</div>";
                    unset($_SESSION['success_message']);
                }
                ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($admin['name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">NIC</label>
                        <input type="text" class="form-control" name="nic" value="<?= htmlspecialchars($admin['nic']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mobile</label>
                        <input type="text" class="form-control" name="mobile" value="<?= htmlspecialchars($admin['mobile']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Registration Number</label>
                        <input type="text" class="form-control" name="registration_number" value="<?= htmlspecialchars($admin['registration_number']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Gender</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" value="Male" <?= $admin['gender'] == 'Male' ? 'checked' : '' ?>>
                            <label class="form-check-label">Male</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" value="Female" <?= $admin['gender'] == 'Female' ? 'checked' : '' ?>>
                            <label class="form-check-label">Female</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="active" <?= $admin['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="pending" <?= $admin['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="disabled" <?= $admin['status'] == 'disabled' ? 'selected' : '' ?>>Disabled</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">Update</button>
                    <a href="manage-batchadmins.php" class="btn btn-secondary">Cancel</a>
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

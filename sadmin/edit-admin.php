<?php
session_start();
require_once '../includes/db-conn.php';

if (!isset($_SESSION['sadmin_id'])) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid request.";
    header("Location: manage-admins.php");
    exit();
}

$admin_id = $_GET['id'];

// Fetch user details
$sql = "SELECT * FROM sadmins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

if (!$admin) {
    $_SESSION['error_message'] = "Admin not found.";
    header("Location: manage-admins.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['name']);
    $email = trim($_POST['email']);
    $nic = trim($_POST['nic']);
    $mobile = trim($_POST['mobile']);

    // Validate inputs
    if (empty($username) || empty($email) || empty($nic) || empty($mobile)) {
        $_SESSION['error_message'] = "All fields are required!";
    } else {
        // Update query
        $sql = "UPDATE sadmins SETname=?, email=?, nic=?, mobile=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $name, $email, $nic, $mobile, $admin_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Admin details updated successfully!";
            header("Location: manage-admins.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error updating admin.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Admin</title>
    <?php include_once("../includes/css-links-inc.php"); ?>
    <style>
        .popup-message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px;
            background-color: #28a745;
            color: white;
            font-weight: bold;
            border-radius: 5px;
            display: none;
            z-index: 9999;
        }
        .error-popup {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <?php include_once("../includes/header2.php"); ?>
    <?php include_once("../includes/sadmin-sidebar.php"); ?>

    <main id="main" class="main">
        <div class="container">
            <h2>Edit Admin</h2>

            <!-- Popup Message -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="popup-message" id="success-popup"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="popup-message error-popup" id="error-popup"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">NIC</label>
                    <input type="text" name="nic" class="form-control" value="<?php echo htmlspecialchars($admin['nic']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mobile</label>
                    <input type="text" name="mobile" class="form-control" value="<?php echo htmlspecialchars($admin['mobile']); ?>" required>
                </div>

                <button type="submit" class="btn btn-success">Update</button>
                <a href="manage-admins.php" class="btn btn-danger">Cancel</a>
            </form>
        </div>
    </main>

    <?php include_once("../includes/footer.php"); ?>
    <?php include_once("../includes/js-links-inc.php"); ?>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let successPopup = document.getElementById("success-popup");
            let errorPopup = document.getElementById("error-popup");

            if (successPopup) {
                successPopup.style.display = "block";
                setTimeout(() => { successPopup.style.display = "none"; }, 1000);
            }
            if (errorPopup) {
                errorPopup.style.display = "block";
                setTimeout(() => { errorPopup.style.display = "none"; }, 1000);
            }
        });
    </script>
</body>
</html>

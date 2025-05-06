<?php
session_start();
require_once("includes/db-conn.php");

if (isset($_GET['token'])) {
    $token = trim(htmlspecialchars($_GET['token'])); // Secure token

    // Check if token exists & is valid (for both users and admins)
    $query = "SELECT user_id, expire_time, role FROM password_reset WHERE token = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        $_SESSION['message'] = "Database error: Unable to prepare statement.";
        $_SESSION['status'] = "error";
        header("Location: index.php");
        exit();
    }

    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $resetRequest = $result->fetch_assoc();
        $userId = $resetRequest['user_id'];
        $expireTime = (int)$resetRequest['expire_time']; // Ensure it's an integer
        $role = $resetRequest['role']; // Get the role (either 'user' or 'admin')

        if (time() < $expireTime) {
            // Token is valid, allow password reset
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $newPassword = $_POST['password'];

                // **Password Strength Validation**
                if (strlen($newPassword) < 8 || !preg_match("/[A-Z]/", $newPassword) || !preg_match("/[a-z]/", $newPassword) || !preg_match("/[0-9]/", $newPassword) || !preg_match("/[\W]/", $newPassword)) {
                    $_SESSION['message'] = "Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
                    $_SESSION['status'] = "error";
                    header("Location: pages-password_reset.php?token=$token");
                    exit();
                }

                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

                // **Update Password**
                if ($role == 'user') {
                    $updateQuery = "UPDATE users SET password = ? WHERE id = ?";
                } else {
                    $updateQuery = "UPDATE admins SET password = ? WHERE id = ?";
                }

                $stmt = $conn->prepare($updateQuery);
                if (!$stmt) {
                    $_SESSION['message'] = "Error preparing update statement.";
                    $_SESSION['status'] = "error";
                    header("Location: pages-password_reset.php?token=$token");
                    exit();
                }

                $stmt->bind_param("si", $hashedPassword, $userId);
                if (!$stmt->execute()) {
                    $_SESSION['message'] = "Error updating password.";
                    $_SESSION['status'] = "error";
                    header("Location: pages-password_reset.php?token=$token");
                    exit();
                }

                // **Delete Token**
                $deleteQuery = "DELETE FROM password_reset WHERE token = ?";
                $stmt = $conn->prepare($deleteQuery);
                if ($stmt) {
                    $stmt->bind_param("s", $token);
                    $stmt->execute();
                }

                $_SESSION['message'] = "Your password has been reset successfully.";
                $_SESSION['status'] = "success";
                header("Location: index.php");
                exit();
            }
        } else {
            $_SESSION['message'] = "This token has expired.";
            $_SESSION['status'] = "error";
            header("Location: pages-password_reset.php");
            exit();
        }
    } else {
        $_SESSION['message'] = "Invalid token.";
        $_SESSION['status'] = "error";
        header("Location: pages-password_reset.php");
        exit();
    }
} else {
    $_SESSION['message'] = "No token provided.";
    $_SESSION['status'] = "error";
    header("Location: pages-password_reset.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - MediQ</title>
    <?php include_once("includes/css-links-inc.php"); ?>
    <style>
        .popup-message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px;
            color: white;
            font-weight: bold;
            border-radius: 5px;
            display: none;
            z-index: 9999;
        }
        .success-popup { background-color: #28a745; }
        .error-popup { background-color: #dc3545; }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['message'])): ?>
        <div class="popup-message <?php echo ($_SESSION['status'] == 'success') ? 'success-popup' : 'error-popup'; ?>" id="popup-alert">
            <?php echo $_SESSION['message']; ?>
        </div>
        <script>
            document.getElementById('popup-alert').style.display = 'block';
            setTimeout(() => { document.getElementById('popup-alert').style.display = 'none'; }, 2000);
        </script>
        <?php unset($_SESSION['message']); unset($_SESSION['status']); ?>
    <?php endif; ?>

    <main>
        <div class="container">
            <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
                            <div class="d-flex justify-content-center py-4">
                                <a href="index.php" class="logo d-flex align-items-center w-auto">
                                    <img src="assets/images/logos/mediq-logo.png" alt="" style="max-height:130px;">
                                </a>
                            </div>

                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="pt-4 pb-2">
                                        <h5 class="card-title text-center pb-0 fs-4">Reset Password</h5>
                                    </div>

                                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?token=' . $token; ?>" method="POST" class="row g-3 needs-validation" novalidate>
                                        <div class="col-12">
                                            <label for="password" class="form-label">New Password</label>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                            <div class="invalid-feedback">Please enter a new password!</div>
                                        </div>

                                        <div class="col-12 mt-3">
                                            <input type="submit" class="btn btn-primary w-100" value="Reset Password">
                                        </div>

                                        <div class="col-12">
                                            <p class="small mb-0" style="font-size:14px;">
                                                <a href="pages-forgotten-password.php">Back to Previous Page</a>
                                            </p>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <?php include_once("includes/footer3.php") ?>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
    <?php include_once("includes/js-links-inc.php") ?>
</body>
</html>

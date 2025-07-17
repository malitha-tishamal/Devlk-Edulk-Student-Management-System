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
    header("Location: manage-lectures.php");
    exit();
}

$lecture_id = $_GET['id'];

// Fetch user details
$user_id = $_SESSION['sadmin_id'];
$sql = "SELECT name, email, nic,mobile,profile_picture FROM sadmins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch current  details
$sql = "SELECT * FROM admins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $lecture_id);
$stmt->execute();
$result = $stmt->get_result();
$lecture = $result->fetch_assoc();
$stmt->close();

if (!$lecture) {
    $_SESSION['error_message'] = "Representer not found.";
    header("Location: manage-batchadmins.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the updated details from the form
    $username = trim($_POST['name']);
    $email = trim($_POST['email']);
    $nic = trim($_POST['nic']);
    $mobile = trim($_POST['mobile']);

    // Validate inputs
    if (empty($username) || empty($email) || empty($nic) || empty($mobile)) {
        $_SESSION['error_message'] = "All fields are required!";
    } else {
        // Update lecture details
        $sql = "UPDATE admins SET name=?, email=?, nic=?, mobile=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $name, $email, $nic, $mobile,  $lecture_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Details updated successfully!";
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
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Edit Representer - Edulk</title>

    <?php include_once("../includes/css-links-inc.php"); ?>
    <style>
        /* Styling for the popup */
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
            display: none; /* Hidden by default */
            z-index: 9999;
        }

        .error-popup {
            background-color: #dc3545;
        }
    </style>
</head>
<body>

    <?php if (isset($_SESSION['status'])): ?>
        <div class="popup-message <?php echo ($_SESSION['status'] == 'success') ? '' : 'error-popup'; ?>" id="popup-alert">
            <?php echo $_SESSION['message']; ?>
        </div>

        <script>
            // Display the popup message
            document.getElementById('popup-alert').style.display = 'block';

            // Automatically hide the popup after 10 seconds
            setTimeout(function() {
                const popupAlert = document.getElementById('popup-alert');
                if (popupAlert) {
                    popupAlert.style.display = 'none';
                }
            }, 1000);

            // If success message, redirect to index.php after 1 seconds
            <?php if ($_SESSION['status'] == 'success'): ?>
                setTimeout(function() {
                    window.location.href = 'manage-lectures.php'; // Redirect after 10 seconds
                }, 1000); // Delay 1 seconds before redirecting
            <?php endif; ?>
        </script>

        <?php
        // Clear session variables after showing the message
        unset($_SESSION['status']);
        unset($_SESSION['message']);
        ?>
    <?php endif; ?>

    <?php include_once("../includes/header.php"); ?>
    <?php include_once("../includes/sadmin-sidebar.php"); ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Edit Representer</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                    <li class="breadcrumb-item">Pages</li>
                    <li class="breadcrumb-item active">Edit Representer</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Edit Representer Details</h5>

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

                            <form method="POST" action="">

                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($lecture['name']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($lecture['email']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="nic" class="form-label">NIC</label>
                                    <input type="text" class="form-control" id="nic" name="nic" value="<?php echo htmlspecialchars($lecture['nic']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="mobile" class="form-label">Mobile</label>
                                    <input type="text" class="form-control" id="mobile" name="mobile" value="<?php echo htmlspecialchars($lecture['mobile']); ?>" required>
                                </div>

                                <button type="submit" class="btn btn-success">Update</button>
                                 <a href="manage-batchadmins.php" class="btn btn-danger">Cancel</a>
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

<?php
// Close database connection
$conn->close();
?>

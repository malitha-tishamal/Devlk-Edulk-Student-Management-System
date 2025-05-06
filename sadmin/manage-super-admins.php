<?php
session_start();
require_once '../includes/db-conn.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['admin_id'];
$sql = "SELECT name, email, nic,mobile,profile_picture FROM admins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch users from the database
$sql = "SELECT id, nic, name, email, mobile, status FROM admins";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Admin Management - Mediq</title>

    <?php include_once("../includes/css-links-inc.php"); ?>
</head>

<body>

    <?php include_once("../includes/header.php") ?>

    <?php include_once("../includes/sadmin-sidebar.php") ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Manage Admins</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                    <li class="breadcrumb-item">Pages</li>
                    <li class="breadcrumb-item active">Manage Admins</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Admin Management</h5>
                            <p>Manage Admins here.</p>

                            <!-- Table with user data -->
                            <table class="table datatable">
                                <thead class="align-middle text-center">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center ">Name</th>
                                        <th class="text-center">Email</th>
                                        <th class="text-center">ID-No</th>
                                        <th class="text-center">Mobile No</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center" colspan="3">Actions</th> <!-- Main Title for Actions -->
                                    </tr>
                                    <tr>
                                        <th colspan="6"></th> <!-- Empty columns for alignment -->
                                        <th class="text-center">Approve</th>
                                        <th class="text-center">Disable</th>
                                        <th class="text-center">Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . $row['id'] . "</td>";
                                            echo "<td>" . $row['name'] . "</td>";
                                            echo "<td>" . $row['email'] . "</td>";
                                            echo "<td>" . $row['nic'] . "</td>";
                                            echo "<td>" . $row['mobile'] . "</td>";

                                            // Status Column with Color
                                            echo "<td>";
                                            $status = strtolower($row['status']); // Convert to lowercase for case insensitivity

                                            if ($status === 'active' || $status === 'approved') {
                                                echo "<span class='btn btn-success btn-sm w-100 text-center'>Approved</span>";
                                            } elseif ($status === 'disabled') {
                                                echo "<span class='btn btn-danger btn-sm w-100 text-center'>Disabled</span>";
                                            } elseif ($status === 'pending') {
                                                echo "<span class='btn btn-warning btn-sm w-100 text-center'>Pending</span>";
                                            } else {
                                                echo "<span class='btn btn-secondary btn-sm w-100 text-center'>" . ucfirst($row['status']) . "</span>";
                                            }
                                            echo "</td>";

                                            // Action Buttons in their respective columns
                                            echo "<td class='text-center'>
                                                    <button class='btn btn-success btn-sm w-100 approve-btn' data-id='" . $row['id'] . "'>Approve</button>
                                                  </td>";
                                            echo "<td class='text-center'>
                                                    <button class='btn btn-warning btn-sm w-100 disable-btn' data-id='" . $row['id'] . "'>Disable</button>
                                                  </td>";
                                            echo "<td class='text-center'>
                                                    <button class='btn btn-danger btn-sm w-100 delete-btn' data-id='" . $row['id'] . "'>Delete</button>
                                                  </td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='9' class='text-center'>No users found.</td></tr>";
                                    }
                                    ?>
                                </tbody>

                            </table>
                            <!-- End Table with user data -->

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include_once("../includes/footer2.php") ?>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <?php include_once("../includes/js-links-inc.php") ?>
    <script type="text/javascript">
      document.addEventListener('DOMContentLoaded', function () {
        const approveButtons = document.querySelectorAll('.approve-btn');
        const disableButtons = document.querySelectorAll('.disable-btn');
        const deleteButtons = document.querySelectorAll('.delete-btn');

        approveButtons.forEach(button => {
            button.addEventListener('click', function () {
                const userId = this.getAttribute('data-id');
                window.location.href = `process-action-superadmin.php?approve_id=${userId}`;
            });
        });

        disableButtons.forEach(button => {
            button.addEventListener('click', function () {
                const userId = this.getAttribute('data-id');
                window.location.href = `process-action-superadmin.php?disable_id=${userId}`;
            });
        });

        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const userId = this.getAttribute('data-id');
                if (confirm("Are you sure you want to delete this user?")) {
                    window.location.href = `process-action-superadmin.php?delete_id=${userId}`;
                }
            });
        });
      });
    </script>

</body>

</html>

<?php
// Close database connection
$conn->close();
?>

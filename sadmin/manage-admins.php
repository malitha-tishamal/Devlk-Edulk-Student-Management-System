<?php
session_start();
require_once '../includes/db-conn.php';

// Redirect if not logged in
if (!isset($_SESSION['sadmin_id'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch logged-in superadmin details
$user_id = $_SESSION['sadmin_id'];
$sql = "SELECT name, email, nic, mobile, profile_picture FROM sadmins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch all admins
$sql = "SELECT * FROM sadmins";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />

    <title>Admin Manage - Edulk</title>

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

        <?php if (isset($_GET['error']) && $_GET['error'] == 'superadmin_protected') : ?>
            <div class="alert alert-danger">Superadmin account cannot be deleted!</div>
        <?php endif; ?>

        <?php if (isset($_GET['success']) && $_GET['success'] == 'admin_deleted') : ?>
            <div class="alert alert-success">Admin account deleted successfully.</div>
        <?php endif; ?>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Admins Management</h5>
                            <p>Manage Admins here.</p>

                            <table class="table datatable">
                                <thead class="align-middle text-center">
                                    <tr>
                                        <th>ID</th>
                                        <th>Profile Picture</th>
                                        <th>Name</th>
                                        <th>NIC</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Account Created</th>
                                        <th>Last Login</th>
                                        <th>Status</th>
                                        <th></th>
                                        <th>Action</th>
                                        <th></th>
                                        <th>Edit</th>
                                    </tr>
                                    <tr>
                                        <th colspan="9" class="text-center"></th>
                                        <th class="text-center">Approve</th>
                                        <th class="text-center">Disable</th>
                                        <th class="text-center">Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $status = strtolower($row['status']);
                                            $is_self = ($row['id'] == $_SESSION['sadmin_id']);
                                            $is_main_admin = ($row['id'] == 1);

                                            // Disable Approve if status is active/approved or self or main admin
                                            $approve_disabled = ($status === 'active' || $status === 'approved' || $is_self || $is_main_admin)
                                                ? "disabled style='opacity: 0.5; pointer-events: none;'"
                                                : "";

                                            // Disable Disable button if status is disabled or self or main admin
                                            $disable_disabled = ($status === 'rejected' || $is_self || $is_main_admin)
                                                ? "disabled style='opacity: 0.5; pointer-events: none;'"
                                                : "";

                                            // Delete disabled if self or main admin
                                            $delete_disabled = ($is_self || $is_main_admin)
                                                ? "disabled style='opacity: 0.5; pointer-events: none;'"
                                                : "";

                                            // Edit disabled if self
                                            $edit_disabled = $is_self
                                                ? "disabled style='opacity: 0.5; pointer-events: none;'"
                                                : "";

                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                            echo "<td><img src='" . htmlspecialchars($row["profile_picture"]) . "' alt='Profile' width='85'></td>";
                                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['nic']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['mobile']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['last_login']) . "</td>";

                                            // Status Display
                                            echo "<td>";
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

                                            // Buttons with dynamic disable
                                            echo "<td class='text-center'>
                                                    <button class='btn btn-success btn-sm w-100 approve-btn' data-id='" . $row['id'] . "' $approve_disabled>Approve</button>
                                                  </td>";
                                            echo "<td class='text-center'>
                                                    <button class='btn btn-warning btn-sm w-100 disable-btn' data-id='" . $row['id'] . "' $disable_disabled>Disable</button>
                                                  </td>";
                                            echo "<td class='text-center'>
                                                    <button class='btn btn-danger btn-sm w-100 delete-btn' data-id='" . $row['id'] . "' $delete_disabled>Delete</button>
                                                  </td>";
                                            echo "<td class='text-center'>
                                                    <a href='edit-admin.php?id=" . $row['id'] . "' class='btn btn-primary btn-sm w-100' $edit_disabled>Edit</a>
                                                  </td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='13' class='text-center'>No users found.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include_once("../includes/footer.php") ?>
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
                    window.location.href = `process-admins.php?approve_id=${userId}`;
                });
            });

            disableButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const userId = this.getAttribute('data-id');
                    window.location.href = `process-admins.php?disable_id=${userId}`;
                });
            });

            deleteButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const userId = this.getAttribute('data-id');
                    if (confirm("Are you sure you want to delete this user?")) {
                        window.location.href = `process-admins.php?delete_id=${userId}`;
                    }
                });
            });
        });
    </script>

</body>

</html>

<?php
$conn->close();
?>

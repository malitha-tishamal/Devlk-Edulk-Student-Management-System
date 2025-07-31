<?php
session_start();
require_once '../includes/db-conn.php';

// Redirect if not logged in
if (!isset($_SESSION['sadmin_id'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['sadmin_id'];
$sql = "SELECT name, email, nic,mobile,profile_picture FROM sadmins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch users from the database
// SQL query to get data
$sql = "SELECT * FROM sadmins";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

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

                            <!-- Table with user data -->
                            <table class="table datatable">
                                <thead class="align-middle text-center">
                                    <tr>
                                        <th>ID</th>
                                        <th>Profile Picture</th>
                                        <th>Name</th>
                                        <th>NIC</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Acccount Created</th>
                                        <th>Last Login</th>
                                        <th>Status</th>
                                        <th></th>
                                        <th>Action</th>
                                        <th></th>
                                         <th>Edit</th>
                                    </tr>
                                    <tr>
                                        <th colspan="9" class="text-center"></th> <!-- Empty columns for alignment -->
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
                                            echo "<td><img src='" . $row["profile_picture"] . "' alt='Profile' width='85'></td>";
                                            echo "<td>" . $row['name'] . "</td> ";
                                            echo "<td>" . $row['nic'] . "</td>";
                                            echo "<td>" . $row['email'] . "</td>";
                                            echo "<td>" . $row['mobile'] . "</td>";
                                            echo "<td>" . $row['created_at'] . "</td>";
                                            echo "<td>" . $row['last_login'] . "</td>";

                                            // Status Column
                                            echo "<td>";
                                            $status = strtolower($row['status']);

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

                                            // Action Buttons
                                           // Check if this row is the logged-in superadmin
                                                $is_self = ($row['id'] == $_SESSION['sadmin_id']);
                                                $is_main_admin = ($row['id'] == 1); // System superadmin

                                                $disable_all = ($is_self || $is_main_admin); // Disable buttons for self OR ID = 1

                                                $btn_disabled = $disable_all ? "disabled style='opacity: 0.5; pointer-events: none;'" : "";
                                                $edit_disabled = $is_self ? "disabled style='opacity: 0.5; pointer-events: none;'" : "";

                                                echo "<td class='text-center'>
                                                        <button class='btn btn-success btn-sm w-100 approve-btn' data-id='" . $row['id'] . "' $btn_disabled>Approve</button>
                                                      </td>";
                                                echo "<td class='text-center'>
                                                        <button class='btn btn-warning btn-sm w-100 disable-btn' data-id='" . $row['id'] . "' $btn_disabled>Disable</button>
                                                      </td>";
                                                echo "<td class='text-center'>
                                                        <button class='btn btn-danger btn-sm w-100 delete-btn' data-id='" . $row['id'] . "' $btn_disabled>Delete</button>
                                                      </td>";
                                                echo "<td class='text-center'>
                                                        <a href='edit-admin.php?id=" . $row['id'] . "' class='btn btn-primary btn-sm w-100' $edit_disabled>Edit</a>
                                                      </td>";



                                           
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='10' class='text-center'>No users found.</td></tr>";
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
// Close database connection
$conn->close();
?>

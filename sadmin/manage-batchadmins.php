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
$sql = "SELECT name, email, nic, mobile, profile_picture FROM sadmins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch admins data
$sql = "SELECT * FROM admins";
$result = $conn->query($sql);

// Fetch distinct batch years from regno (e.g., 23 -> 2023)
$batch_query = "SELECT DISTINCT SUBSTRING_INDEX(SUBSTRING_INDEX(regno, '/', -2), '/', 1) AS year_suffix FROM admins WHERE regno LIKE 'GAL/IT/%'";
$batch_result = $conn->query($batch_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Batch Admin Manage - Edulk</title>
    <?php include_once("../includes/css-links-inc.php"); ?>
</head>
<body>

<?php include_once("../includes/header.php") ?>
<?php include_once("../includes/sadmin-sidebar.php") ?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Manage Batch Representers</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                <li class="breadcrumb-item">Pages</li>
                <li class="breadcrumb-item active">Manage Representers</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Representers Management</h5>
                        <p>Manage Representers here.</p>

                        <!-- Batch Filter Dropdown -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="batchFilter" class="form-label">Filter by Batch (e.g., 23):</label>
                                <select id="batchFilter" class="form-select">
                                    <option value="">All</option>
                                    <?php
                                    // Extract first number after GAL/IT/
                                    $batch_result = $conn->query("SELECT DISTINCT SUBSTRING_INDEX(SUBSTRING_INDEX(regno, '/', 3), '/', -1) AS batch FROM admins WHERE regno LIKE 'GAL/IT/%'");
                                    while ($row = $batch_result->fetch_assoc()) {
                                        echo "<option value='{$row['batch']}'>{$row['batch']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>


                        <!-- Table -->
                        <table class="table datatable">
                            <thead class="align-middle text-center">
                                <tr>
                                    <th>ID</th>
                                    <th>Profile Picture</th>
                                    <th>Name</th>
                                    <th>NIC</th>
                                    <th>Reg No</th>
                                    <th>Email</th>
                                    <th>Gender</th>
                                    <th>Mobile</th>
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
                                    echo "<td>{$row['id']}</td>";
                                    echo "<td><img src='../admin/{$row['profile_picture']}' alt='Profile' width='50'></td>";
                                    echo "<td>{$row['name']}</td>";
                                    echo "<td>{$row['nic']}</td>";
                                    echo "<td class='regno'>{$row['regno']}</td>";
                                    echo "<td>{$row['email']}</td>";
                                    echo "<td>{$row['gender']}</td>";
                                    echo "<td>{$row['mobile']}</td>";

                                    echo "<td>";
                                    $status = strtolower($row['status']);
                                    if ($status === 'active' || $status === 'approved') {
                                        echo "<span class='btn btn-success btn-sm w-100'>Approved</span>";
                                    } elseif ($status === 'disabled') {
                                        echo "<span class='btn btn-danger btn-sm w-100'>Disabled</span>";
                                    } elseif ($status === 'pending') {
                                        echo "<span class='btn btn-warning btn-sm w-100'>Pending</span>";
                                    } else {
                                        echo "<span class='btn btn-secondary btn-sm w-100'>" . ucfirst($status) . "</span>";
                                    }
                                    echo "</td>";

                                    echo "<td class='text-center'><button class='btn btn-success btn-sm w-100 approve-btn' data-id='{$row['id']}'>Approve</button></td>";
                                    echo "<td class='text-center'><button class='btn btn-warning btn-sm w-100 disable-btn' data-id='{$row['id']}'>Disable</button></td>";
                                    echo "<td class='text-center'><button class='btn btn-danger btn-sm w-100 delete-btn' data-id='{$row['id']}'>Delete</button></td>";
                                     // Edit Profile Button
                                            echo "<td class='text-center'>
                                                    <a href='edit-representer.php?id=" . $row['id'] . "' class='btn btn-primary btn-sm w-100'>Edit</a>
                                                  </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='12' class='text-center'>No users found.</td></tr>";
                            }
                            ?>
                            </tbody>
                        </table>
                        <!-- End Table -->
                    </div>
                </div>

            </div>
        </div>
    </section>
</main>

<?php include_once("../includes/footer.php") ?>
<a href="#" class="back-to-top d-flex align-items-center justify-content-center">
    <i class="bi bi-arrow-up-short"></i>
</a>

<?php include_once("../includes/js-links-inc.php"); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const batchFilter = document.getElementById('batchFilter');
    const rows = document.querySelectorAll('table tbody tr');

    batchFilter.addEventListener('change', function () {
        const selectedBatch = this.value;
        rows.forEach(row => {
            const regnoCell = row.querySelector('.regno');
            if (regnoCell) {
                const regno = regnoCell.textContent.trim().toLowerCase();
                const match = regno.match(/gal\/it\/(\d{2})/);
                const batch = match ? match[1] : '';
                row.style.display = (!selectedBatch || batch === selectedBatch) ? '' : 'none';
            }
        });
    });

    // Action buttons
    document.querySelectorAll('.approve-btn').forEach(btn =>
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            window.location.href = `process-batchadmins.php?approve_id=${id}`;
        })
    );

    document.querySelectorAll('.disable-btn').forEach(btn =>
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            window.location.href = `process-batchadmins.php?disable_id=${id}`;
        })
    );

    document.querySelectorAll('.delete-btn').forEach(btn =>
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            if (confirm("Are you sure you want to delete this user?")) {
                window.location.href = `process-batchadmins.php?delete_id=${id}`;
            }
        })
    );
});
</script>

</body>
</html>

<?php $conn->close(); ?>

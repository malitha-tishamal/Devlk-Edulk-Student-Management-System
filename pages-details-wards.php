<?php
session_start();
require_once 'includes/db-conn.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$sql = "SELECT name, email, nic, mobile FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch ward details
$wards = [];
$sql = "SELECT * FROM ward ORDER BY id DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $wards[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Manage Wards - Mediq</title>
    <?php include_once("includes/css-links-inc.php"); ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
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

    <?php include_once("includes/header.php"); ?>
    <?php include_once("includes/sadmin-sidebar2.php"); ?>

    <!-- Displaying session messages -->
    <?php if (isset($_SESSION['status'])): ?>
        <div class="popup-message <?php echo ($_SESSION['status'] == 'success') ? '' : 'error-popup'; ?>" id="popup-alert">
            <?php echo $_SESSION['message']; ?>
        </div>

        <script>
            document.getElementById('popup-alert').style.display = 'block';
            setTimeout(() => { document.getElementById('popup-alert').style.display = 'none'; }, 5000);
            <?php if ($_SESSION['status'] == 'success'): ?>
                setTimeout(() => { window.location.href = 'manage-wards.php'; }, 5000);
            <?php endif; ?>
        </script>

        <?php unset($_SESSION['status']); unset($_SESSION['message']); ?>
    <?php endif; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Details Wards</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Details Wards</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Ward List</h5>
                            <table id="wardTable" class="table datatable">
                                <thead class="align-middle text-center">
                                    <tr>
                                        <th>ID</th>
                                        <th>Ward Name</th>
                                        <th>Description</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($wards as $ward): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ward['id']); ?></td>
                                            <td><?php echo htmlspecialchars($ward['ward_name']); ?></td>
                                            <td><?php echo htmlspecialchars($ward['description']); ?></td>
                                            <td><?php echo htmlspecialchars($ward['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <a href="pages-add-new-ward.php" class="btn btn-primary mt-3">Add New Ward</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include_once("includes/footer.php"); ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#wardTable').DataTable();
        });
    </script>
</body>
</html>

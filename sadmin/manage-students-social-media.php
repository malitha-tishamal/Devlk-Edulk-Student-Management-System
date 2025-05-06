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

// Fetch filtering parameters from GET request
$search = isset($_GET['search']) ? $_GET['search'] : '';
$study_year = isset($_GET['study_year']) ? $_GET['study_year'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Build the SQL query with filters
$sql = "SELECT * FROM students WHERE 1";

// Apply search filter if provided
if ($search !== '') {
    $sql .= " AND (name LIKE '%$search%' OR regno LIKE '%$search%')";
}

// Apply study year filter if provided
if ($study_year !== '') {
    $sql .= " AND study_year = '$study_year'";
}

// Apply status filter if provided
if ($status !== '') {
    $sql .= " AND status = '$status'";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Active Students Manage - EduWide</title>

    <?php include_once("../includes/css-links-inc.php"); ?>
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>

    <?php include_once("../includes/header.php") ?>
    <?php include_once("../includes/sadmin-sidebar.php") ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Manage Active Students</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                    <li class="breadcrumb-item">Pages</li>
                    <li class="breadcrumb-item active">Manage Active Students</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Active Students Management</h5>
                            <p>Manage Active Students here.</p>

                            <!-- Search Bar and Filters -->
                            <form method="GET" action="">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <input type="text" name="search" class="form-control" placeholder="Search by Name or Reg ID" value="<?php echo htmlspecialchars($search); ?>">
                                    </div>

                                    <div class="col-md-3">
                                        <select name="status" class="form-select">
                                            <option value="">All Status</option>
                                            <option value="active" <?php echo ($status == "active" ? 'selected' : ''); ?>>Active</option>
                                            <option value="pending" <?php echo ($status == "pending" ? 'selected' : ''); ?>>Pending</option>
                                            <option value="disabled" <?php echo ($status == "disabled" ? 'selected' : ''); ?>>Disabled</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                    </div>
                                </div>
                            </form>

                            <!-- Table with user data -->
                            <table class="table datatable">
                                <thead class="align-middle text-center">
                                    <tr>
                                        <th>ID</th>
                                        <th>Profile Picture</th>
                                        <th>Username</th>
                                        <th>Reg ID</th>
                                        <th>Blog</th>
                                        <th>Facebook</th>
                                        <th>LinkedIn</th>
                                        <th>Github</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr class='align-middle text-center'>";
                                            echo "<td>" . $row['id'] . "</td>";
                                            echo "<td><img src='../" . $row["profile_picture"] . "' alt='Profile' width='50' height='50' style='object-fit: cover; border-radius: 50%;'></td>";
                                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['regno']) . "</td>";

                                            // Blog
                                            echo "<td>";
                                            if (!empty($row['blog'])) {
                                                echo "<a href='" . htmlspecialchars($row['blog']) . "' target='_blank'><i class='bi bi-globe fs-5'></i></a>";
                                            }
                                            echo "</td>";

                                            // Facebook
                                            echo "<td>";
                                            if (!empty($row['facebook'])) {
                                                echo "<a href='" . htmlspecialchars($row['facebook']) . "' target='_blank'><i class='bi bi-facebook fs-5 text-primary'></i></a>";
                                            }
                                            echo "</td>";

                                            // LinkedIn
                                            echo "<td>";
                                            if (!empty($row['linkedin'])) {
                                                echo "<a href='" . htmlspecialchars($row['linkedin']) . "' target='_blank'><i class='bi bi-linkedin fs-5 text-info'></i></a>";
                                            }
                                            echo "</td>";

                                            // Github
                                            echo "<td>";
                                            if (!empty($row['github'])) {
                                                echo "<a href='" . htmlspecialchars($row['github']) . "' target='_blank'><i class='bi bi-github fs-5 text-dark'></i></a>";
                                            }
                                            echo "</td>";

                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='8' class='text-center'>No users found.</td></tr>";
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
                    window.location.href = `process-students.php?approve_id=${userId}`;
                });
            });

            disableButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const userId = this.getAttribute('data-id');
                    window.location.href = `process-students.php?disable_id=${userId}`;
                });
            });

            deleteButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const userId = this.getAttribute('data-id');
                    if (confirm("Are you sure you want to delete this user?")) {
                        window.location.href = `process-students.php?delete_id=${userId}`;
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

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

// Get filters
$search = $_GET['search'] ?? '';
$study_year = $_GET['study_year'] ?? '';
$status = $_GET['status'] ?? '';

// Build query
$sql = "SELECT * FROM students WHERE 1";
if ($search !== '') {
    $searchSafe = $conn->real_escape_string($search);
    $sql .= " AND (name LIKE '%$searchSafe%' OR regno LIKE '%$searchSafe%')";
}
if ($study_year !== '') {
    $sql .= " AND LEFT(SUBSTRING_INDEX(regno, '/', -3), 2) = '$study_year'";
}
if ($status !== '') {
    $sql .= " AND status = '$status'";
}

$result = $conn->query($sql);

// Get all distinct study years from regno
$yearQuery = "SELECT DISTINCT LEFT(SUBSTRING_INDEX(regno, '/', -3), 2) AS year FROM students ORDER BY year DESC";
$yearResult = $conn->query($yearQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Manage Students - EduWide</title>
  <?php include_once("../includes/css-links-inc.php"); ?>
</head>
<body>
  <?php include_once("../includes/header.php") ?>
  <?php include_once("../includes/sadmin-sidebar.php") ?>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Manage Students</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item active">Manage Students</li>
        </ol>
      </nav>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Student Management</h5>

              <!-- Filters -->
              <form method="GET" action="">
                <div class="row mb-3">
                  <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by Name or Reg ID" value="<?= htmlspecialchars($search); ?>">
                  </div>
                  <div class="col-md-3">
                    <select name="study_year" class="form-select">
                      <option value="">All Years</option>
                      <?php
                      if ($yearResult->num_rows > 0) {
                        while ($y = $yearResult->fetch_assoc()) {
                          $yearVal = $y['year'];
                          $selected = ($study_year == $yearVal) ? 'selected' : '';
                          echo "<option value='$yearVal' $selected>20$yearVal</option>";
                        }
                      }
                      ?>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <select name="status" class="form-select">
                      <option value="">All Status</option>
                      <option value="active" <?= ($status == 'active') ? 'selected' : '' ?>>Active</option>
                      <option value="pending" <?= ($status == 'pending') ? 'selected' : '' ?>>Pending</option>
                      <option value="disabled" <?= ($status == 'disabled') ? 'selected' : '' ?>>Disabled</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Filter</button>
                  </div>
                </div>
              </form>

              <!-- Table -->
              <table class="table datatable text-center align-middle">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Profile</th>
                    <th>Name</th>
                    <th>Reg ID</th>
                    <th>NIC</th>
                    <th>Email</th>
                    <th>Gender</th>
                    <th>Address</th>
                    <th>Now</th>
                    <th>Mobile</th>
                    <th>Home</th>
                    <th>Status</th>
                    <th></th>
                    <th>Action</th>
                    <th></th>
                    <th>Edit</th>
                </tr>
                <tr>
                    <th colspan="12" class="text-center"></th> <!-- Empty columns for alignment -->
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
                      echo "<td><img src='../{$row['profile_picture']}' width='40'></td>";
                      echo "<td>{$row['name']}</td>";
                      echo "<td>{$row['regno']}</td>";
                      echo "<td>{$row['nic']}</td>";
                      echo "<td>{$row['email']}</td>";
                      echo "<td>{$row['gender']}</td>";
                      echo "<td>{$row['address']}</td>";
                      echo "<td>{$row['nowstatus']}</td>";
                      echo "<td>{$row['mobile']}</td>";
                      echo "<td>{$row['mobile2']}</td>";

                      // Status Badge
                      echo "<td>";
                      switch (strtolower($row['status'])) {
                        case 'active':
                        case 'approved':
                          echo "<span class='btn btn-success btn-sm w-100'>Approved</span>";
                          break;
                        case 'disabled':
                          echo "<span class='btn btn-danger btn-sm w-100'>Disabled</span>";
                          break;
                        case 'pending':
                          echo "<span class='btn btn-warning btn-sm w-100'>Pending</span>";
                          break;
                        default:
                          echo "<span class='btn btn-secondary btn-sm w-100'>" . ucfirst($row['status']) . "</span>";
                      }
                      echo "</td>";

                      echo "<td><button class='btn btn-success btn-sm w-100 approve-btn' data-id='{$row['id']}'>Approve</button></td>";
                      echo "<td><button class='btn btn-warning btn-sm w-100 disable-btn' data-id='{$row['id']}'>Disable</button></td>";
                      echo "<td><button class='btn btn-danger btn-sm w-100 delete-btn' data-id='{$row['id']}'>Delete</button></td>";
                      echo "<td class='text-center'>
                            <a href='edit-student.php?id=" . $row['id'] . "' class='btn btn-primary btn-sm w-100'>Edit</a>
                          </td>";
                      echo "</tr>";
                    }
                  } else {
                    echo "<tr><td colspan='15'>No students found.</td></tr>";
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

  <?php include_once("../includes/footer.php"); ?>
  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  <?php include_once("../includes/js-links-inc.php"); ?>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = btn.getAttribute('data-id');
          window.location.href = `process-students.php?approve_id=${id}`;
        });
      });
      document.querySelectorAll('.disable-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = btn.getAttribute('data-id');
          window.location.href = `process-students.php?disable_id=${id}`;
        });
      });
      document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = btn.getAttribute('data-id');
          if (confirm("Are you sure you want to delete this student?")) {
            window.location.href = `process-students.php?delete_id=${id}`;
          }
        });
      });
    });
  </script>
</body>
</html>

<?php $conn->close(); ?>

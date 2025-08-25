<?php
session_start();
require_once '../includes/db-conn.php';


$user_id = $_SESSION['sadmin_id'] ?? 0;
$sql = "SELECT * FROM sadmins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// ====== PASSWORD PROTECTION ======
$PAGE_PASSWORD = "00000000"; // <-- Change this to your desired password

if (isset($_POST['page_password'])) {
    if ($_POST['page_password'] === $PAGE_PASSWORD) {
        $_SESSION['student_logs_access'] = true;
    } else {
        $error = "❌ Incorrect password!";
    }
}

// ===================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>Student Logs - Edulk</title>
<?php include_once ("../includes/css-links-inc.php"); ?>
<style>
/* Table Styles */
table { border-collapse: collapse; width: 100%; font-size:14px; }
th, td { border: 1px solid #e9ecef; padding: 10px; text-align: center; vertical-align: middle; }
th { background-color: #f8f9fa; font-weight: 600; color: #495057; }
tr:nth-child(even) { background-color: #fdfdfd; }
tr:hover { background-color: #f1f3f5; transition: 0.2s; }

/* Profile Picture */
.profile-pic { width: 120px; height: 120px; object-fit: cover;}

/* Map Section */
#map { height: 450px; width: 100%; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); margin-top: 20px; }

/* Icon */
.open-map-icon { font-size: 18px; color: #0d6efd; transition: color 0.2s; }
.open-map-icon:hover { color: #dc3545; }

.card { border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
.card-title { font-weight: 600; color: #212529; }

/* Password box inside dashboard */
.password-box { background:#fff; padding:40px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.1); width:400px; margin: 40px auto; text-align:center; }
.password-box input { padding:10px; width:100%; margin-bottom:15px; border:1px solid #ced4da; border-radius:5px; }
.password-box button { padding:10px 20px; background:#0d6efd; color:#fff; border:none; border-radius:5px; cursor:pointer; width:100%; }
.password-box button:hover { background:#0b5ed7; }
.error { color:red; margin-bottom:10px; font-weight:500; }
</style>
</head>
<body>

<?php include_once ("../includes/header.php") ?>
<?php include_once ("../includes/sadmin-sidebar.php") ?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>📊 Student Logs</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Student Logs</li>
            </ol>
        </nav>
    </div>

    <section class="section profile">
        <div class="row">
            <div class="col-12">

            <?php if (!isset($_SESSION['student_logs_access']) || $_SESSION['student_logs_access'] !== true): ?>
                <!-- 🔒 Password Form (inside dashboard layout) -->
                <div class="password-box">
                    <h2>🔒 Enter Page Password</h2>
                    <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
                    <form method="post">
                        <input type="password" name="page_password" placeholder="Password" required>
                        <button type="submit">Access</button>
                    </form>
                </div>

            <?php else: ?>

                <?php
                // ==== Fetch admin details ====
                $user_id = $_SESSION['sadmin_id'] ?? 0;
                $sql = "SELECT * FROM sadmins WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $stmt->close();

                // ==== Fetch student logs ====
                $sql_logs = "SELECT l.*, s.name AS student_name, s.profile_picture 
                            FROM students_logs l
                            LEFT JOIN students s ON l.student_id = s.id OR l.regno = s.regno
                            ORDER BY l.login_time DESC";
                $logs_result = $conn->query($sql_logs);
                $logs = [];
                while($row = $logs_result->fetch_assoc()) {
                    $logs[] = $row;
                }

                // Map JSON
                $map_data = array_filter($logs, function($l) {
                    return !empty($l['latitude']) && !empty($l['longitude']);
                });
                $map_json = json_encode($map_data);
                ?>

                <!-- Logs Table -->
                <div class="card mb-4">
                    <div class="card-body pt-3">
                        <h5 class="card-title">All Student Logs</h5>

                        <!-- 🔍 Search Bar -->
                        <div class="mb-3">
                            <input type="text" id="searchInput" class="form-control w-50" placeholder="Search logs...">
                        </div>

                        <div class="table-responsive">
                            <table id="logsTable" class="table align-datatable">
                                <thead>
                                    <tr>
                                        <th>Profile</th>
                                        <th>Student Name</th>
                                        <?php
                                        $columns = array_keys($logs[0] ?? []);
                                        foreach($columns as $col) {
                                            if(!in_array($col, ['latitude','longitude','profile_picture','student_name'])) {
                                                echo "<th>".ucfirst($col)."</th>";
                                            }
                                        }
                                        echo "<th>Location</th>";
                                        ?>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach($logs as $log): ?>
                                    <tr>
                                        <!-- Profile Picture -->
                                        <td>
                                            <?php if(!empty($log['profile_picture'])): ?>
                                                <img src="../<?= htmlspecialchars($log['profile_picture']) ?>" class="profile-pic" alt="Profile">
                                            <?php else: ?>
                                                <img src="../uploads/default.png" class="profile-pic" alt="Profile">
                                            <?php endif; ?>
                                        </td>
                                        <!-- Student Name -->
                                        <td><?= htmlspecialchars($log['student_name']) ?></td>

                                        <?php foreach($columns as $col): ?>
                                            <?php if(!in_array($col, ['latitude','longitude','profile_picture','student_name'])): ?>
                                                <td><?= htmlspecialchars($log[$col]) ?></td>
                                            <?php endif; ?>
                                        <?php endforeach; ?>

                                        <!-- Location -->
                                        <td>
                                            <?php if(!empty($log['latitude']) && !empty($log['longitude'])): ?>
                                                <a href="https://www.google.com/maps?q=<?= $log['latitude'] ?>,<?= $log['longitude'] ?>" 
                                                   target="_blank" title="Open in Google Maps">
                                                   <i class="bi bi-geo-alt-fill open-map-icon"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">No Data</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <script>
                // Simple search filter
                document.getElementById("searchInput").addEventListener("keyup", function() {
                    let filter = this.value.toLowerCase();
                    let rows = document.querySelectorAll("#logsTable tbody tr");
                    rows.forEach(row => {
                        let text = row.innerText.toLowerCase();
                        row.style.display = text.includes(filter) ? "" : "none";
                    });
                });
                </script>

                <!-- Google Map -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">🌍 Student Locations</h5>
                        <div id="map"></div>
                    </div>
                </div>

                <script>
                // Map data from PHP
                const students = <?= $map_json ?>;

                function initMap() {
                    const map = new google.maps.Map(document.getElementById("map"), {
                        zoom: 2,
                        center: { lat: 7.8731, lng: 80.7718 } // Sri Lanka
                    });

                    students.forEach(student => {
                        const lat = parseFloat(student.latitude);
                        const lng = parseFloat(student.longitude);
                        if (!isNaN(lat) && !isNaN(lng)) {
                            const marker = new google.maps.Marker({
                                position: { lat: lat, lng: lng },
                                map: map,
                                title: student.student_name,
                                icon: "https://maps.google.com/mapfiles/ms/icons/red-dot.png"
                            });

                            const infoWindow = new google.maps.InfoWindow({
                                content: `<div style="font-size:14px;">
                                            <strong>${student.student_name}</strong><br>
                                            📍 Lat: ${lat}, Lng: ${lng}<br>
                                            <a href="https://www.google.com/maps?q=${lat},${lng}" 
                                               target="_blank" style="color:#0d6efd;font-weight:500;">
                                               ➡ Open in Google Maps
                                            </a>
                                          </div>`
                            });

                            marker.addListener('click', () => {
                                infoWindow.open(map, marker);
                            });
                        }
                    });
                }
                </script>

                <!-- Replace with your actual Google Maps API Key -->
                <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap" async defer></script>

            <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php include_once ("../includes/footer.php") ?>
<a href="#" class="back-to-top d-flex align-items-center justify-content-center">
    <i class="bi bi-arrow-up-short"></i>
</a>
<?php include_once ("../includes/js-links-inc.php") ?>

</body>
</html>

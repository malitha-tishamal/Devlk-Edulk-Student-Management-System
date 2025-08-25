<?php
session_start();
require_once '../includes/db-conn.php';

// ====== PASSWORD PROTECTION ======
$PAGE_PASSWORD = "00000000"; // <-- Change this
if (isset($_POST['page_password'])) {
    if ($_POST['page_password'] === $PAGE_PASSWORD) {
        $_SESSION['sadmin_logs_access'] = true;
    } else {
        $error = "Incorrect password!";
    }
}

$user_id = $_SESSION['sadmin_id'] ?? 0;
$sql = "SELECT * FROM sadmins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch logs
$sql_logs = "
    SELECT l.*, lec.profile_picture
    FROM lectures_logs l
    LEFT JOIN lectures lec ON l.lecture_id = lec.id
    ORDER BY l.login_time DESC
";
$stmt = $conn->prepare($sql_logs);
$stmt->execute();
$result = $stmt->get_result();

$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}

// Show password page if not authorized
if (!isset($_SESSION['sadmin_logs_access']) || $_SESSION['sadmin_logs_access'] !== true) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Enter Password</title>
        <?php include_once ("../includes/css-links-inc.php"); ?>
    </head>
    <body>
        <?php include_once ("../includes/header.php"); ?>
        <?php include_once ("../includes/sadmin-sidebar.php"); ?>

        <main class="main d-flex justify-content-center align-items-center" style="min-height: 80vh;">
            <div class="card shadow-sm" style="max-width: 400px; width: 100%;">
                <div class="card-body text-center">
                    <h5 class="card-title mb-3">🔒 Enter Page Password</h5>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <form method="post">
                        <div class="mb-3">
                            <input type="password" name="page_password" class="form-control form-control-lg" placeholder="Password" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-unlock-fill me-2"></i> Access Logs
                        </button>
                    </form>
                </div>
            </div>
        </main>

        <?php include_once ("../includes/footer.php"); ?>
        <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
        <?php include_once ("../includes/js-links-inc.php"); ?>
    </body>
    </html>
    <?php
    exit();
}

// Prepare map data
$map_data = array_filter($logs, fn($l) => !empty($l['latitude']) && !empty($l['longitude']));
$map_json = json_encode($map_data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lectures Logs - Edulk</title>
<?php include_once ("../includes/css-links-inc.php"); ?>
<style>
table { border-collapse: collapse; width: 100%; font-size:14px; }
th, td { border: 1px solid #e9ecef; padding: 8px; text-align: center; vertical-align: middle; }
th { background-color: #f8f9fa; font-weight: 600; color: #495057; }
tr:nth-child(even) { background-color: #fdfdfd; }
tr:hover { background-color: #f1f3f5; transition: 0.2s; }
.profile-pic { width: 120px; height: 120px; object-fit: cover; border-radius: 50%; }
#map { height: 450px; width: 100%; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); margin-top: 20px; }
.open-map-icon { font-size: 18px; color: #0d6efd; transition: color 0.2s; }
.open-map-icon:hover { color: #dc3545; }
.card { border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 20px; }
.card-title { font-weight: 600; color: #212529; }
</style>
</head>
<body>

<?php include_once ("../includes/header.php"); ?>
<?php include_once ("../includes/sadmin-sidebar.php"); ?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>📊 Lectures Logs</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Lectures Logs</li>
            </ol>
        </nav>
    </div>

    <section class="section profile">
        <div class="row">
            <div class="col-12">

                <!-- Logs Table -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">All Lectures Logs</h5>
                        <input type="text" id="searchInput" class="form-control w-50 mb-3" placeholder="Search logs...">

                        <div class="table-responsive">
                            <table id="logsTable" class="table align-datatable">
                                <thead>
                                    <tr>
                                        <th>Profile</th>
                                        <?php
                                        $columns = array_keys($logs[0] ?? []);
                                        foreach($columns as $col) {
                                            if(!in_array($col, ['latitude','longitude','profile_picture'])) echo "<th>".ucfirst($col)."</th>";
                                        }
                                        echo "<th>Location</th>";
                                        ?>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach($logs as $log): ?>
                                    <tr>
                                        <td>
                                            <?php 
                                            $profile = !empty($log['profile_picture']) 
                                                ? "../lectures/{$log['profile_picture']}" 
                                                : "../uploads/default.png"; 
                                            ?>
                                            <img src="<?= htmlspecialchars($profile) ?>" class="profile-pic" alt="Profile">
                                        </td>
                                        <?php foreach($columns as $col): ?>
                                            <?php if(!in_array($col,['latitude','longitude','profile_picture'])): ?>
                                                <td><?= htmlspecialchars($log[$col]) ?></td>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        <td>
                                            <?php if(!empty($log['latitude']) && !empty($log['longitude'])): ?>
                                                <a href="https://www.google.com/maps?q=<?= $log['latitude'] ?>,<?= $log['longitude'] ?>" target="_blank" title="Open in Google Maps">
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

                <!-- Search Script -->
                <script>
                document.getElementById("searchInput").addEventListener("keyup", function() {
                    let filter = this.value.toLowerCase();
                    document.querySelectorAll("#logsTable tbody tr").forEach(row => {
                        row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
                    });
                });
                </script>

                <!-- Map -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">🌍 Superadmin Locations</h5>
                        <div id="map"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</main>

<?php include_once ("../includes/footer.php"); ?>
<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
<?php include_once ("../includes/js-links-inc.php"); ?>

<script>
const logs = <?= $map_json ?>;
function initMap() {
    const map = new google.maps.Map(document.getElementById("map"), {
        zoom: 2,
        center: { lat: 7.8731, lng: 80.7718 } // Sri Lanka
    });

    logs.forEach(log => {
        const lat = parseFloat(log.latitude);
        const lng = parseFloat(log.longitude);
        if(!isNaN(lat) && !isNaN(lng)){
            const marker = new google.maps.Marker({
                position: {lat, lng},
                map: map,
                title: log.sadmin_name,
                icon: "https://maps.google.com/mapfiles/ms/icons/red-dot.png"
            });

            const infoWindow = new google.maps.InfoWindow({
                content: `<div style="font-size:14px;">
                            <strong>${log.real_sadmin_name ?? log.sadmin_name}</strong><br>
                            📍 Lat: ${lat}, Lng: ${lng}<br>
                            Login: ${log.login_time}<br>
                            Logout: ${log.logout_time}<br>
                            <a href="https://www.google.com/maps?q=${lat},${lng}" target="_blank" style="color:#0d6efd;">➡ Open in Google Maps</a>
                          </div>`
            });

            marker.addListener('click', () => infoWindow.open(map, marker));
        }
    });
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap" async defer></script>
</body>
</html>

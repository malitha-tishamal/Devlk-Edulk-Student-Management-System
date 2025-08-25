<?php
session_start();
require_once '../includes/db-conn.php';

// --- Extra password check before showing logs ---
if (!isset($_SESSION['sadmin_id'])) {
    header("Location: index.php");
    exit();
}

// --- If password OK, show logs ---
$user_id = $_SESSION['sadmin_id'];
$sql = "SELECT * FROM sadmins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Optional: extra password protection for this page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['access_pass'])) {
    if ($_POST['access_pass'] === "00000000") { // change this password
        $_SESSION['guestlogs_access'] = true;
    } else {
        $error = "Invalid password!";
    }
}

if (!isset($_SESSION['guestlogs_access'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Secure Access</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <?php include_once("../includes/css-links-inc.php"); ?>
    </head>
    <body>
    <?php include_once("../includes/header.php") ?>
    <?php include_once("../includes/sadmin-sidebar.php") ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Guest Logs - Secure Access</h1>
        </div>
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Enter Access Password</h5>
                    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
                    <form method="post">
                        <div class="mb-3">
                            <input type="password" name="access_pass" class="form-control" placeholder="Enter Password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Unlock Logs</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <?php include_once("../includes/footer.php") ?>
    <?php include_once("../includes/js-links-inc.php") ?>
    </body>
    </html>
    <?php
    exit();
}

// --- If password OK, show logs ---
$query = "SELECT * FROM user_logs WHERE user_id IS NULL ORDER BY accessed_at DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Guest Access Logs</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <?php include_once("../includes/css-links-inc.php"); ?>
    <style>
        .table th, .table td {
            vertical-align: middle;
            font-size: 13px;
        }
        .table thead {
            background-color: #f8f9fa;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .map-icon {
            color: #007bff;
            font-size: 1.2rem;
        }
        .map-icon:hover {
            color: #dc3545;
        }
    </style>
</head>
<body>
<?php include_once("../includes/header.php") ?>
<?php include_once("../includes/sadmin-sidebar.php") ?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Guest Access Logs</h1>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">All Guest Visits</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User ID</th>
                                <th>IP Address</th>
                                <th>User Agent</th>
                                <th>Device Type</th>
                                <th>OS</th>
                                <th>Browser</th>
                                <th>Browser Version</th>
                                <th>Language</th>
                                <th>Device Vendor</th>
                                <th>Device Model</th>
                                <th>Orientation</th>
                                <th>Touch</th>
                                <th>Pixel Ratio</th>
                                <th>Connection</th>
                                <th>Viewport</th>
                                <th>Screen Res</th>
                                <th>Timezone</th>
                                <th>Battery</th>
                                <th>Status</th>
                                <th>Visit Location</th>
                                <th>Referrer</th>
                                <th>Current URL</th>
                                <th>Session ID</th>
                                <th>Accessed At</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($log = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['id']) ?></td>
                                <td><?= htmlspecialchars($log['user_id'] ?? 'Guest') ?></td>
                                <td><?= htmlspecialchars($log['ip_address']) ?></td>
                                <td style="max-width:250px;"><?= htmlspecialchars($log['user_agent']) ?></td>
                                <td><?= htmlspecialchars($log['device_type']) ?></td>
                                <td><?= htmlspecialchars($log['os']) ?></td>
                                <td><?= htmlspecialchars($log['browser']) ?></td>
                                <td><?= htmlspecialchars($log['browser_version']) ?></td>
                                <td><?= htmlspecialchars($log['language']) ?></td>
                                <td><?= htmlspecialchars($log['device_vendor']) ?></td>
                                <td><?= htmlspecialchars($log['device_model']) ?></td>
                                <td><?= htmlspecialchars($log['orientation']) ?></td>
                                <td><?= htmlspecialchars($log['touch_support']) ?></td>
                                <td><?= htmlspecialchars($log['pixel_ratio']) ?></td>
                                <td><?= htmlspecialchars($log['connection_type']) ?></td>
                                <td><?= htmlspecialchars($log['viewport_size']) ?></td>
                                <td><?= htmlspecialchars($log['screen_resolution']) ?></td>
                                <td><?= htmlspecialchars($log['timezone']) ?></td>
                                <td><?= htmlspecialchars($log['battery_level']) ?></td>
                                <td><?= htmlspecialchars($log['online_status']) ?></td>
                                <td>
                                    <?php if (!empty($log['latitude']) && !empty($log['longitude'])): ?>
                                        <a href="https://www.google.com/maps?q=<?= urlencode($log['latitude']) ?>,<?= urlencode($log['longitude']) ?>"
                                           target="_blank" title="Open in Google Maps">
                                            <i class="fas fa-map-marker-alt map-icon"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No Data</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($log['referrer']) ?></td>
                                <td><?= htmlspecialchars($log['current_url']) ?></td>
                                <td><?= htmlspecialchars($log['session_id']) ?></td>
                                <td><?= htmlspecialchars($log['accessed_at']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include_once("../includes/footer.php") ?>
<?php include_once("../includes/js-links-inc.php") ?>
</body>
</html>
<?php $conn->close(); ?>

<?php
session_start();
require_once '../includes/db-conn.php';

// Redirect if not logged in
if (!isset($_SESSION['sadmin_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['sadmin_id'];
$sql = "SELECT * FROM sadmins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch guest log data
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
            font-size: 14px;
        }
        .log-icon {
            color: #6c757d;
        }
        .table thead {
            background-color: #f8f9fa;
        }
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body>
<?php include_once("../includes/header.php") ?>
<?php include_once("../includes/sadmin-sidebar.php") ?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Guest Access Logs</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Guest Logs</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">All Guest Visits</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User ID</th>
                                <th>IP Address</th>
                                <th>User Agent</th>
                                <th>Device Type</th>
                                <th>OS</th>
                                <th>Browser</th>
                                <th>Language</th>
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
                                <td style="max-width: 300px; overflow-wrap: break-word;"><?= htmlspecialchars($log['user_agent']) ?></td>
                                <td><?= htmlspecialchars($log['device_type']) ?></td>
                                <td><?= htmlspecialchars($log['os']) ?></td>
                                <td><?= htmlspecialchars($log['browser']) ?></td>
                                <td><?= htmlspecialchars($log['language']) ?></td>
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

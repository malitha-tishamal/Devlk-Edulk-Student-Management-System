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
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Query for Line and Bar chart data (Only current & last two months)
$chartQuery = "
    SELECT MONTH(release_time) AS month, YEAR(release_time) AS year, SUM(item_count) AS usage_count
    FROM releases
    WHERE release_time >= DATE_FORMAT(NOW() - INTERVAL 2 MONTH, '%Y-%m-01') 
    GROUP BY YEAR(release_time), MONTH(release_time)
    ORDER BY year DESC, month DESC
";

$chartStmt = $conn->prepare($chartQuery);
$chartStmt->execute();
$chartResult = $chartStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Home - MediQ</title>

    <?php include_once("includes/css-links-inc.php"); ?>

    <!-- Google Charts -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', { 'packages': ['corechart', 'line', 'bar'] });
        google.charts.setOnLoadCallback(drawCharts);

        function drawCharts() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Month');
            data.addColumn('number', 'Usage Count');

            <?php 
            $chartData = [];
            while ($row = $chartResult->fetch_assoc()) {
                $month = date('F', strtotime("{$row['year']}-{$row['month']}-01"));
                $chartData[] = "['$month', {$row['usage_count']}]";
            }
            echo "data.addRows([" . implode(",", $chartData) . "]);";
            ?>

            // Line Chart options
            var lineOptions = {
                title: 'Antibiotic Usage (Last 3 Months)',
                curveType: 'function',
                legend: { position: 'bottom' },
                hAxis: { title: 'Month' },
                vAxis: { title: 'Usage Count' }
            };

            // Bar Chart options
            var barOptions = {
                title: 'Antibiotic Usage (Last 3 Months)',
                chartArea: { width: '50%' },
                hAxis: { title: 'Usage Count', minValue: 0 },
                vAxis: { title: 'Month' }
            };

            // Draw the charts
            var lineChart = new google.visualization.LineChart(document.getElementById('linechart'));
            lineChart.draw(data, lineOptions);

            var barChart = new google.visualization.BarChart(document.getElementById('barchart'));
            barChart.draw(data, barOptions);
        }
    </script>

    <style>
        #linechart, #barchart { width: 100%; height: 400px; margin: auto; }
    </style>
    <style>
        #piechart { width: 50%; height: 400px; margin: auto; }
        @media print { .no-print { display: none; } }
    </style>
    <script> function printPage() { window.print(); } </script>
</head>
<body>

    <?php include_once("includes/header.php") ?>
    <?php include_once("includes/user-sidebar.php") ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Home</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="">Home</a></li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div>
                            <h6 class="card-title text-center">Welcome, <?php echo htmlspecialchars($user['name']); ?>. This is an Antibiotic Usage Summary for the last 3 months.</h6>
                        </div>
                        <div class="card-body d-flex flex-column flex-md-row">
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Antibiotic Usage (Line Chart)</h5>
                                        <div id="linechart"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Antibiotic Usage (Bar Chart)</h5>
                                        <div id="barchart"></div>
                                    </div>
                                </div>
                                 <div class="col-sm-5">
                                    <button class="btn btn-danger mt-4 ml-2 print-btn no-print" onclick="printPage()">Print Report</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main><!-- End #main -->

    <?php include_once("includes/footer.php") ?>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <?php include_once("includes/js-links-inc.php") ?>

</body>
</html>

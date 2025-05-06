<?php
session_start();
require_once '../includes/db-conn.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['admin_id'];
$sql2 = "SELECT name, email, nic, mobile, profile_picture FROM admins WHERE id = ?";
$stmt = $conn->prepare($sql2);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$startMonth = isset($_GET['start_month']) ? $_GET['start_month'] : "01";
$endMonth = isset($_GET['end_month']) ? $_GET['end_month'] : date('m');

$startDate = "$year-$startMonth-01"; // Default: first day of start month
$endDate = "$year-$endMonth-31";  // Default: last possible day of end month

// If custom dates are provided, override month range
if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $startDate = $_GET['start_date'];
    $endDate = $_GET['end_date'];
}





// Fetch antibiotic usage by ward using your provided query with month filter
$sql = "
    SELECT 
    CASE 
        WHEN ward_name = '1 & 2 - Pediatrics - Combined' THEN '1 (Pediatrics)'
        WHEN ward_name IN ('3 - Surgical Prof - Female', '5 - Surgical Prof - Male') THEN '3+5 (Surgical Prof)'
        WHEN ward_name IN ('4 - Surgery - Male', '7 - Surgical Prof - Female') THEN '4+7 (Surgery)'
        WHEN ward_name = '6 - Surgery - Combined' THEN '6 (Surgery)'
        WHEN ward_name IN ('8 - Neuro-Surgery - Female', '37 - Neuro-Surgery - Male') THEN '8+37 (Neuro-Surgery)'
        WHEN ward_name = '9 - Surgery - Combined' THEN '9 (Surgery)'
        WHEN ward_name = '10 - Surgery' THEN '10 (Surgery)'
        WHEN ward_name = '38 (Neuro-Surgery)' THEN '38 (Neuro-Surgery)'
        WHEN ward_name IN ('11 - Medicine Prof - Female', '12 - Medicine Prof - Male') THEN '11+12 (Medicine Prof)'
        WHEN ward_name IN ('14 - Medicine - Male', '15 - Medicine - Female') THEN '14+15 (Medicine)'
        WHEN ward_name IN ('16 - Medicine - Male', '17 - Medicine - Female') THEN '16+17 (Medicine)'
        WHEN ward_name IN ('18 - Psychiatry - Male', '23 - Psychiatry - Female') THEN '18+23 (Psychiatry)'
        WHEN ward_name IN ('19 - Medicine - Male', '21 - Medicine - Female') THEN '19+21 (Medicine)'
        WHEN ward_name IN ('20 - Orthopedic - Female', '22 - Orthopedic - Male') THEN '20+22 (Orthopedic)'
        WHEN ward_name IN ('30 - ENT - Male', '31 - ENT - Female') THEN '30+31 (ENT)'
        WHEN ward_name = '24 - Neurology - Combined' THEN '24 (Neurology)'
        WHEN ward_name = '26 - Oro-Maxillary Facial - Combined' THEN '26 (Oro-Maxillary Facial)'
        WHEN ward_name = '36 (Pediatrics) - Combined' THEN '36 (Pediatrics)'
        WHEN ward_name IN ('25 - Dermatology - Female', '27 - Dermatology - Male') THEN '25+27 (Dermatology)'
        WHEN ward_name IN ('28 - Oncology - Male', '29 - Oncology - Female') THEN '28+29 (Oncology)'
        WHEN ward_name IN ('32 - Ophthalmology - Female', '33 - Ophthalmology - Male') THEN '32+33 (Ophthalmology)'
        WHEN ward_name IN ('34 - Medicine - Male', '35 - Medicine - Female') THEN '34+35 (Medicine)'
        WHEN ward_name IN ('39 & 40 - Cardiology') THEN '39+40 (Cardiology)'
        WHEN ward_name IN ('41, 42 & 43 - Maliban Rehabilitation') THEN '41+42+43 (Maliban Rehabilitation)'
        WHEN ward_name IN ('44 - Cardio-Thoracic - Female', '45 - Cardio-Thoracic - Male') THEN '44+45 (Cardio-Thoracic)'
        WHEN ward_name IN ('46 & 47 - GU Surgery - Male') THEN '46+47 (GU Surgery)'
        WHEN ward_name IN ('48 - Onco-Surgery - Female', 'Ward 59', '49 - Onco-Surgery - Male') THEN '48+49 (Onco-Surgery)'
        WHEN ward_name = '50 - Pediatric Oncology - Combined' THEN '50 (Pediatric Oncology)'
        WHEN ward_name IN ('51 & 52 - Pediatric Surgery') THEN '51+52 (Pediatric Surgery)'
        WHEN ward_name IN ('53 - Ophthalmology - Male', '54 - Ophthalmology - Female') THEN '53+54 (Ophthalmology)'
        WHEN ward_name = '55 - Rheumatology - Combined' THEN '55 (Rheumatology)'
        WHEN ward_name IN ('58 - Emergency/ETC - Male', '59 - Emergency/ETC - Female') THEN '58+59 (Emergency/ETC)'
        WHEN ward_name = '60 - ETC Pead - Combined' THEN '60 (ETC Pead)'
        WHEN ward_name IN ('61 & 62 - Bhikku') THEN '61+62 (Bhikku)'
        WHEN ward_name = '65 - Palliative' THEN '65 (Palliative)'
        WHEN ward_name = '67 - Stroke' THEN '67 (Stroke)'
        WHEN ward_name IN ('68 & 69 - Respiratory') THEN '68+69 (Respiratory)'
        WHEN ward_name IN ('70 - Nephrology', '71 - Nephrology - Male', '73 - Nephrology - Female') THEN '70+71+73+74 (Nephrology)'
        WHEN ward_name = '72 - Vascular Surgery - Combined' THEN '72 (Vascular Surgery)'
        WHEN ward_name LIKE '%ICU%' THEN 'All ICU'
        WHEN ward_name LIKE '%Theater%' THEN 'All Theater'
        ELSE ward_name 
    END AS ward_group,
    antibiotic_name,
    dosage,
    SUM(item_count) AS total_items
FROM releases
WHERE release_time BETWEEN ? AND ?

GROUP BY ward_group, antibiotic_name, dosage;
";



$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $startDate, $endDate);

$stmt->execute();
$result2 = $stmt->get_result();
$stmt->close()

// Start HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Antibiotic Usage Monitor</title>
    <!-- DataTable CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">
    <?php include_once("../includes/css-links-inc.php"); ?>
    <!-- jQuery -->
    <script type="text/javascript" charset="utf-8" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTable JS -->
    <script type="text/javascript" charset="utf-8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
</head>
<body>

<main id="main" class="main">
        <div class="pagetitle">
            <h1>Usage Details Ward Wise</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                    <li class="breadcrumb-item">Pages</li>
                    <li class="breadcrumb-item active">Antibiotic Usage Details</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Antibiotic Usage Details</h5>

                            <form method="get" action="">
    <div class="form-group mb-3">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="filter_type" id="filter_by_month" value="month" <?= (!isset($_GET['filter_type']) || $_GET['filter_type'] == 'month') ? 'checked' : '' ?>>
            <label class="form-check-label" for="filter_by_month">Filter by Month Range</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="filter_type" id="filter_by_date" value="date" <?= (isset($_GET['filter_type']) && $_GET['filter_type'] == 'date') ? 'checked' : '' ?>>
            <label class="form-check-label" for="filter_by_date">Filter by Custom Date Range</label>
        </div>
    </div>

    <div id="month_range_filters" class="<?= (isset($_GET['filter_type']) && $_GET['filter_type'] == 'date') ? 'd-none' : '' ?>">
        <div class="form-group d-flex align-items-center gap-2 mb-3">
            <label for="year">Select Year</label>
            <select id="year" name="year" class="form-control w-25">
                <?php
                $currentYear = date('Y');
                for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                    $selected = (isset($_GET['year']) && $_GET['year'] == $y) ? "selected" : "";
                    echo "<option value='$y' $selected>$y</option>";
                }
                ?>
            </select>

            <label for="start_month">Start Month</label>
            <select id="start_month" name="start_month" class="form-control w-25">
                <?php
                $months = [
                    '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
                    '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
                    '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
                ];
                $selectedStartMonth = isset($_GET['start_month']) ? $_GET['start_month'] : "01";
                foreach ($months as $num => $name) {
                    $selected = ($num == $selectedStartMonth) ? "selected" : "";
                    echo "<option value='$num' $selected>$name</option>";
                }
                ?>
            </select>

            <label for="end_month">End Month</label>
            <select id="end_month" name="end_month" class="form-control w-25">
                <?php
                $selectedEndMonth = isset($_GET['end_month']) ? $_GET['end_month'] : date('m');
                foreach ($months as $num => $name) {
                    $selected = ($num == $selectedEndMonth) ? "selected" : "";
                    echo "<option value='$num' $selected>$name</option>";
                }
                ?>
            </select>
        </div>
    </div>

    <div id="date_range_filters" class="<?= (!isset($_GET['filter_type']) || $_GET['filter_type'] == 'month') ? 'd-none' : '' ?>">
        <div class="form-group d-flex align-items-center gap-2 mb-3">
            <label for="start_date">Start Date</label>
            <input type="date" id="start_date" name="start_date" class="form-control w-25" value="<?= isset($_GET['start_date']) ? $_GET['start_date'] : '' ?>">

            <label for="end_date">End Date</label>
            <input type="date" id="end_date" name="end_date" class="form-control w-25" value="<?= isset($_GET['end_date']) ? $_GET['end_date'] : '' ?>">
        </div>
    </div>

    <button type="submit" class="btn btn-primary mt-2">Filter</button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get radio buttons and filter divs
    const filterByMonth = document.getElementById('filter_by_month');
    const filterByDate = document.getElementById('filter_by_date');
    const monthRangeFilters = document.getElementById('month_range_filters');
    const dateRangeFilters = document.getElementById('date_range_filters');
    
    // Add event listeners
    filterByMonth.addEventListener('change', function() {
        if (this.checked) {
            monthRangeFilters.classList.remove('d-none');
            dateRangeFilters.classList.add('d-none');
        }
    });
    
    filterByDate.addEventListener('change', function() {
        if (this.checked) {
            monthRangeFilters.classList.add('d-none');
            dateRangeFilters.classList.remove('d-none');
        }
    });
});
</script>



                            <?php include_once("../includes/header.php") ?>
                            <?php include_once("../includes/sadmin-sidebar.php") ?>

                            <table id="antibioticUsageTable" class="display table">
                                <thead class="align-middle text-center">
                                    <tr>
                                        <th>#</th>
                                        <th>Antibiotic Name</th>
                                        <th>Dosage</th>
                                        <th>Count</th>
                                        <th>Units</th>
                                        <th>Percentage (%)</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                <?php 
                                if ($result2->num_rows > 0) {
                                    $rowNumber = 1;
                                    $previousWard = "";
                                    $totalUnits = 0;
                                    $wardUsage = []; // Store per-ward total units

                                    // First pass: Calculate total units
                                    while ($row = $result2->fetch_assoc()) {
                                        $wardName = $row['ward_group'];
                                        $dosage = strtolower($row['dosage']);
                                        $itemCount = $row['total_items'];
                                        $usageInGrams = 0;

                                        if (preg_match('/(\d+)\s*mg/', $dosage, $matches)) {
                                            $mgValue = (int)$matches[1];
                                            $usageInGrams = ($mgValue / 1000) * $itemCount;
                                        } elseif (preg_match('/(\d+)\s*g/', $dosage, $matches)) {
                                            $gValue = (float)$matches[1];
                                            $usageInGrams = $gValue * $itemCount;
                                        }

                                        $usageInUnits = $usageInGrams; // 1g = 1 unit
                                        $totalUnits += $usageInUnits;

                                        // Store per-ward usage
                                        if (!isset($wardUsage[$wardName])) {
                                            $wardUsage[$wardName] = 0;
                                        }
                                        $wardUsage[$wardName] += $usageInUnits;
                                    }

                                    $result2->data_seek(0); // Reset result pointer for display loop

                                    // Second pass: Display data
                                    while ($row = $result2->fetch_assoc()) {
                                        $wardName = $row['ward_group'];
                                        $antibioticName = $row['antibiotic_name'];
                                        $dosage = strtolower($row['dosage']);
                                        $itemCount = $row['total_items'];
                                        $usageInGrams = 0;

                                        if (preg_match('/(\d+)\s*mg/', $dosage, $matches)) {
                                            $mgValue = (int)$matches[1];
                                            $usageInGrams = ($mgValue / 1000) * $itemCount;
                                        } elseif (preg_match('/(\d+)\s*g/', $dosage, $matches)) {
                                            $gValue = (float)$matches[1];
                                            $usageInGrams = $gValue * $itemCount;
                                        }

                                        $usageInUnits = $usageInGrams;
                                        $percentageUsage = ($totalUnits > 0) ? ($usageInUnits / $totalUnits) * 100 : 0;

                                        // Group by ward name
                                        if ($wardName != $previousWard) {
                                            // Ward heading row
                                            echo "<tr><td colspan='6' class='text-center card-title' style='background-color: #f8f9fa; font-weight: bold;'>$wardName</td></tr>";
                                            $previousWard = $wardName;
                                        }
                                ?>
                                        <tr>
                                            <td class='text-center'><?php echo $rowNumber; ?></td>
                                            <td class='text-center'><?php echo $antibioticName; ?></td>
                                            <td class='text-center'><?php echo $dosage; ?></td>
                                            <td class='text-center'><?php echo number_format($itemCount); ?></td>
                                            <td class='text-center'><?php echo number_format($usageInUnits, 2); ?>g</td>
                                            <td class='text-center'><?php echo number_format($percentageUsage, 2); ?>%</td>
                                        </tr>
                                <?php 
                                        $rowNumber++;
                                    }

                                    // Display total usage per ward and the overall total at the end
                                    //echo "<tr><td colspan='6' class='text-center' style='font-weight: bold;'>Total Units: " . number_format($totalUnits, 2) . "g</td></tr>";

                                    // Display each ward's total usage
                                    //foreach ($wardUsage as $ward => $wardTotal) {
                                      //  echo "<tr><td colspan='6' class='text-center' style='background-color: #e9ecef;'>$ward - Total Usage: " . number_format($wardTotal, 2) . "g</td></tr>";
                                   // }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center'>No data available for the selected period, ward, and type</td></tr>";
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

<?php include_once("../includes/js-links-inc.php") ?>
    <?php include_once("../includes/footer.php") ?>
</body>
</html>

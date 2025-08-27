<?php
session_start();
require_once '../includes/db-conn.php';

// Check admin login
if (!isset($_SESSION['sadmin_id'])) {
    header("Location: index.php");
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

// --- Handle filters ---
$where = [];
$params = [];
$types  = "";

// Semester filter
if (!empty($_GET['semester'])) {
    $where[] = "sub.semester = ?";
    $params[] = $_GET['semester'];
    $types   .= "s";
}

// Subject filter
if (!empty($_GET['subject_id'])) {
    $where[] = "r.subject_id = ?";
    $params[] = $_GET['subject_id'];
    $types   .= "i";
}

// Student filter (by name or regno)
if (!empty($_GET['student'])) {
    $where[] = "(s.name LIKE ? OR s.regno LIKE ?)";
    $params[] = "%" . $_GET['student'] . "%";
    $params[] = "%" . $_GET['student'] . "%";
    $types   .= "ss";
}

// Batch Year filter (first 2 digits of regno year)
if (!empty($_GET['batch_year'])) {
    $where[] = "SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(s.regno, '/', 3), '/', -1), 1, 2) = ?";
    $params[] = $_GET['batch_year'];
    $types .= "s";
}

// Build WHERE clause
$where_sql = "";
if (count($where) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where);
}

// Main query
$sql = "SELECT 
            s.id AS student_id,
            s.name AS student_name,
            s.regno,
            s.email,
            sub.semester,
            sub.name AS subject_name,
            r.title AS recording_title,
            rsp.remaining_play_count,
            rsp.plays_left,
            rsp.last_played
        FROM recording_student_plays rsp
        LEFT JOIN students s ON rsp.student_id = s.id
        LEFT JOIN recordings r ON rsp.recording_id = r.id
        LEFT JOIN subjects sub ON r.subject_id = sub.id
        $where_sql
        ORDER BY rsp.last_played DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch subjects for dropdown
$subjects = $conn->query("SELECT * FROM subjects ORDER BY name ASC");

// Fetch distinct semesters
$semesters = $conn->query("SELECT DISTINCT semester FROM subjects ORDER BY semester ASC");

// Fetch distinct batch years
$batch_years_result = $conn->query("
    SELECT DISTINCT SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(regno, '/', 3), '/', -1), 1, 2) AS batch_year
    FROM students
    ORDER BY batch_year ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Recording Plays - Edulk</title>
    <?php include_once("../includes/css-links-inc.php"); ?>
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
        }
        
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: none;
            margin-bottom: 24px;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #eaeaea;
            padding: 20px 25px;
            border-radius: 12px 12px 0 0 !important;
            font-weight: 600;
            font-size: 18px;
            color: var(--dark);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .card-header i {
            margin-right: 10px;
            color: var(--primary);
        }
        
        .card-body {
            padding: 25px;
        }
        
        .filter-section {
            background: #f9fafc;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .filter-title {
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--dark);
            display: flex;
            align-items: center;
        }
        
        .filter-title i {
            margin-right: 8px;
            color: var(--primary);
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        
        .custom-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 14px;
        }
        
        .custom-table thead th {
            background-color: #f3f7ff;
            color: var(--dark);
            font-weight: 600;
            padding: 15px 12px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .custom-table tbody td {
            padding: 14px 12px;
            border-bottom: 1px solid #edf2f7;
            vertical-align: middle;
        }
        
        .custom-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .custom-table tbody tr:hover {
            background-color: #f8fafc;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .plays-high {
            background-color: #e6f4ee;
            color: #0d6832;
        }
        
        .plays-medium {
            background-color: #fef6e6;
            color: #9c5c10;
        }
        
        .plays-low {
            background-color: #feece8;
            color: #c13515;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            border-radius: 6px;
            padding: 8px 20px;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
            border-color: var(--secondary);
        }
        
        .btn-outline-secondary {
            border-radius: 6px;
            padding: 8px 20px;
            font-weight: 500;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--dark);
        }
        
        .form-control, .form-select {
            border-radius: 6px;
            padding: 10px 12px;
            border: 1px solid #d2d6dc;
        }
        
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
            border-color: var(--primary);
        }
        
        .results-count {
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .last-played {
            color: var(--gray);
            font-size: 13px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray);
        }
        
        .empty-state i {
            font-size: 56px;
            margin-bottom: 15px;
            color: #d1d5db;
        }
        
        .empty-state p {
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .card-body {
                padding: 15px;
            }
            
            .filter-section {
                padding: 15px;
            }
            
            .btn-responsive {
                width: 100%;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>

    <?php include_once("../includes/header.php") ?>
<?php include_once("../includes/sadmin-sidebar.php") ?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Recording Plays</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item">Analytics</li>
                <li class="breadcrumb-item active">Recording Plays</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div><i class="bi bi-play-btn"></i> Student Recording Play Tracker</div>
                        <button class="btn btn-outline-secondary btn-sm" onclick="exportToCSV()">
                            <i class="bi bi-download me-1"></i> Export
                        </button>
                    </div>
                    <div class="card-body">


<div class="container-fluid">
    <!-- Filters -->
    <div class="filter-section">
        <div class="filter-title"><i class="bi bi-funnel"></i> Filter Results</div>
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Semester</label>
                <select name="semester" class="form-select">
                    <option value="">All Semesters</option>
                    <?php while($sem = $semesters->fetch_assoc()) { ?>
                        <option value="<?= htmlspecialchars($sem['semester']); ?>" 
                            <?= (isset($_GET['semester']) && $_GET['semester'] == $sem['semester']) ? 'selected' : ''; ?>>
                            Semester <?= htmlspecialchars($sem['semester']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select">
                    <option value="">All Subjects</option>
                    <?php 
                    $subjects->data_seek(0);
                    while($sub = $subjects->fetch_assoc()) { ?>
                        <option value="<?= $sub['id']; ?>" 
                            <?= (isset($_GET['subject_id']) && $_GET['subject_id'] == $sub['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($sub['code'] . " - " . $sub['name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Student (Name/Reg No)</label>
                <input type="text" name="student" class="form-control" 
                       placeholder="Search student..." 
                       value="<?= isset($_GET['student']) ? htmlspecialchars($_GET['student']) : ''; ?>">
            </div>

            <!--div class="col-md-3">
                <label class="form-label">Batch Year</label>
                <select name="batch_year" class="form-select">
                    <option value="">All Years</option>
                    <?php 
                    $batch_years_result->data_seek(0);
                    while($by = $batch_years_result->fetch_assoc()) { ?>
                        <option value="<?= $by['batch_year']; ?>" 
                            <?= (isset($_GET['batch_year']) && $_GET['batch_year'] == $by['batch_year']) ? 'selected' : ''; ?>>
                            <?= $by['batch_year']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div-->

            <div class="col-md-12 d-flex justify-content-end gap-2">
                <a href="recording_students.php" class="btn btn-outline-secondary">Clear</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter-circle me-1"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>

    <?php
    $total_records = $result->num_rows;
    $has_filters = !empty($_GET['semester']) || !empty($_GET['subject_id']) || !empty($_GET['student']) || !empty($_GET['batch_year']);
    ?>
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="results-count">
            <?php if ($has_filters): ?>
                <?= $total_records ?> result<?= $total_records != 1 ? 's' : '' ?> found
            <?php else: ?>
                Total Records: <?= $total_records ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Reg No</th>
                    <th>Semester</th>
                    <th>Subject</th>
                    <th>Recording</th>
                    <th>Remaining Plays</th>
                    <th>Plays Left</th>
                    <th>Last Played</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0) { ?>
                    <?php while($row = $result->fetch_assoc()) { 
                        // Determine badge class based on plays left
                        $plays_class = '';
                        if ($row['plays_left'] > 5) {
                            $plays_class = 'plays-high';
                        } elseif ($row['plays_left'] > 2) {
                            $plays_class = 'plays-medium';
                        } else {
                            $plays_class = 'plays-low';
                        }
                    ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($row['student_name']); ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($row['email']); ?></div>
                            </td>
                            <td><?= htmlspecialchars($row['regno']); ?></td>
                            <td>Sem <?= htmlspecialchars($row['semester']); ?></td>
                            <td><?= htmlspecialchars($row['subject_name']); ?></td>
                            <td><?= htmlspecialchars($row['recording_title']); ?></td>
                            <td><?= $row['remaining_play_count']; ?></td>
                            <td>
                                <span class="status-badge <?= $plays_class ?>">
                                    <?= $row['plays_left']; ?> left
                                </span>
                            </td>
                            <td class="last-played">
                                <?php if ($row['last_played']): ?>
                                    <?= date('M j, Y g:i A', strtotime($row['last_played'])); ?>
                                <?php else: ?>
                                    <span class="text-muted">Never</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <p>No recording play data found</p>
                                <?php if ($has_filters): ?>
                                    <a href="recording_students.php" class="btn btn-primary">Clear Filters</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include_once("../includes/footer.php") ?>
<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
<?php include_once("../includes/js-links-inc.php") ?>

<script>
function exportToCSV() {
    // Create a temporary form to submit the export request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'export-recording-plays.php';
    
    // Add current filter parameters as hidden inputs
    const params = new URLSearchParams(window.location.search);
    for (const [key, value] of params) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    }
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
});
</script>
</body>
</html>
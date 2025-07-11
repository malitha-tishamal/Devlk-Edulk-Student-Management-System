<?php
session_start();
require_once 'includes/db-conn.php';

// Enable error reporting (for dev only)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$user_id = $_SESSION['student_id'] ?? null;

// Fetch user details if needed
if ($user_id) {
    $sql = "SELECT name, email, nic, mobile, profile_picture FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
    }
}

// Get semesters
$semesters = [];
$semQuery = $conn->query("SELECT DISTINCT semester FROM subjects ORDER BY semester ASC");
if ($semQuery) {
    while ($row = $semQuery->fetch_assoc()) {
        $semesters[] = $row['semester'];
    }
}

$selectedSemester = $_GET['semester'] ?? '';
$selectedSubject = $_GET['subject'] ?? '';

// Helper: file icon HTML with color based on extension
function getFileIconColored($ext) {
    $ext = strtolower($ext);
    switch ($ext) {
        case 'pdf':
            return '<i class="fa-solid fa-file-pdf" style="color:#d9534f;"></i>'; // red
        case 'doc':
        case 'docx':
            return '<i class="fa-solid fa-file-word" style="color:#2a64bc;"></i>'; // blue
        case 'xls':
        case 'xlsx':
            return '<i class="fa-solid fa-file-excel" style="color:#218838;"></i>'; // green
        case 'ppt':
        case 'pptx':
            return '<i class="fa-solid fa-file-powerpoint" style="color:#f0ad4e;"></i>'; // orange
        default:
            return '<i class="fa-solid fa-file" style="color:#6c757d;"></i>'; // gray
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>View Files - EduWide</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <?php include_once("includes/css-links-inc.php"); ?>
    <style>
        .subject-heading {
            font-weight: bold;
            color: #007bff;
            margin-top: 30px;
        }
        .week-subheading {
            font-weight: 600;
            color: #1a73e8;
            margin-left: 20px;
            margin-top: 20px;
        }
        table {
            margin-left: 40px;
        }
    </style>
</head>
<body>
<?php include_once("includes/header.php") ?>
<?php include_once("includes/student-sidebar.php") ?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Available Files</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Available Files</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="mb-4 row g-3">
                    <div class="col-md-4">
                        <label for="semester">Select Semester:</label>
                        <select id="semester" name="semester" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Select Semester --</option>
                            <?php foreach ($semesters as $sem): ?>
                                <option value="<?= htmlspecialchars($sem) ?>" <?= ($sem == $selectedSemester) ? 'selected' : '' ?>>
                                    Semester <?= htmlspecialchars($sem) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($selectedSemester !== ''): ?>
                        <?php
                        $subjects = [];
                        $subjectQuery = $conn->prepare("SELECT id, name FROM subjects WHERE semester = ? ORDER BY name ASC");
                        if ($subjectQuery) {
                            $subjectQuery->bind_param("s", $selectedSemester);
                            $subjectQuery->execute();
                            $subjectResult = $subjectQuery->get_result();
                            while ($row = $subjectResult->fetch_assoc()) {
                                $subjects[] = $row;
                            }
                            $subjectQuery->close();
                        }
                        ?>
                        <div class="col-md-4">
                            <label for="subject">Select Subject:</label>
                            <select id="subject" name="subject" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Select Subject --</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?= htmlspecialchars($subject['id']) ?>" <?= ($subject['id'] == $selectedSubject) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($subject['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </form>

                <?php if ($selectedSemester !== '' && $selectedSubject !== ''): ?>
                    <?php
                    // Get subject name
                    $stmt = $conn->prepare("SELECT name FROM subjects WHERE id = ?");
                    $subjectName = 'Unknown Subject';
                    if ($stmt) {
                        $stmt->bind_param("i", $selectedSubject);
                        $stmt->execute();
                        $res = $stmt->get_result();
                        if ($row = $res->fetch_assoc()) {
                            $subjectName = $row['name'];
                        }
                        $stmt->close();
                    }

                    echo "<h5 class='subject-heading'>" . htmlspecialchars($subjectName) . "</h5>";

                    // Fetch files for this subject
                    $stmt = $conn->prepare("SELECT title, category, filename FROM tuition_files WHERE subject_id = ? AND status = 'active' ORDER BY uploaded_at DESC");
                    $notesByWeek = [];
                    $otherFiles = [];
                    if ($stmt) {
                        $stmt->bind_param("i", $selectedSubject);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        while ($file = $result->fetch_assoc()) {
                            // Check if category is Notes and title has "Week X"
                            if (strtolower($file['category']) === 'notes' && preg_match('/^(Week\s*\d+)/i', $file['title'], $matches)) {
                                $weekKey = $matches[1]; // e.g. "Week 1"
                                $notesByWeek[$weekKey][] = $file;
                            } else {
                                $otherFiles[] = $file;
                            }
                        }
                        $stmt->close();
                    }

                    // Sort weeks naturally (Week 1, Week 2, Week 10 etc)
                    if (!empty($notesByWeek)) {
                        ksort($notesByWeek, SORT_NATURAL | SORT_FLAG_CASE);
                    }

                    // Output Notes grouped by week
                    foreach ($notesByWeek as $week => $files) {
                        echo "<h6 class='week-subheading'>" . htmlspecialchars($week) . "</h6>";
                        echo '<table class="table table-bordered mb-4">';
                        echo '<thead><tr><th>Title</th><th>Category</th><th>Extension</th><th>Download</th></tr></thead><tbody>';
                        foreach ($files as $f) {
                            $ext = pathinfo($f['filename'], PATHINFO_EXTENSION);
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($f['title']) . "</td>";
                            echo "<td>" . htmlspecialchars($f['category']) . "</td>";
                            echo "<td>" . getFileIconColored($ext) . " " . strtoupper($ext) . "</td>";
                            echo '<td><a href="../uploads/' . urlencode($f['filename']) . '" target="_blank" rel="noopener">Download</a></td>';
                            echo "</tr>";
                        }
                        echo '</tbody></table>';
                    }

                    // Output other files if any
                    if (!empty($otherFiles)) {
                        echo "<h6 class='week-subheading'>Other Files</h6>";
                        echo '<table class="table table-bordered mb-4">';
                        echo '<thead><tr><th>Title</th><th>Category</th><th>Extension</th><th>Download</th></tr></thead><tbody>';
                        foreach ($otherFiles as $f) {
                            $ext = pathinfo($f['filename'], PATHINFO_EXTENSION);
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($f['title']) . "</td>";
                            echo "<td>" . htmlspecialchars($f['category']) . "</td>";
                            echo "<td>" . getFileIconColored($ext) . " " . strtoupper($ext) . "</td>";
                            echo '<td><a href="../uploads/' . urlencode($f['filename']) . '" target="_blank" rel="noopener">Download</a></td>';
                            echo "</tr>";
                        }
                        echo '</tbody></table>';
                    }
                    ?>

                <?php elseif ($selectedSemester !== ''): ?>
                    <p>Please select a subject to view files.</p>
                <?php else: ?>
                    <p>Please select a semester to view available files.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php include_once("includes/footer.php") ?>
<?php include_once("includes/js-links-inc.php") ?>
</body>
</html>

<?php $conn->close(); ?>

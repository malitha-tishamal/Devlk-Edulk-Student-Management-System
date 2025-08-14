<?php
session_start();
require_once 'includes/db-conn.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$user_id = $_SESSION['student_id'] ?? null;
if ($user_id) {
    $stmt = $conn->prepare("SELECT name, email, nic, mobile, profile_picture FROM students WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

$semesters = [];
$semQuery = $conn->query("SELECT DISTINCT semester FROM subjects ORDER BY semester ASC");
if ($semQuery) while ($row = $semQuery->fetch_assoc()) $semesters[] = $row['semester'];

$selectedSemester = $_GET['semester'] ?? '';
$selectedSubject = $_GET['subject'] ?? '';

function getFileIconColored($ext) {
    $ext = strtolower($ext);
    switch ($ext) {
        case 'pdf': return '<i class="fa-solid fa-file-pdf" style="color:#e74c3c;"></i>';
        case 'doc': case 'docx': return '<i class="fa-solid fa-file-word" style="color:#3498db;"></i>';
        case 'xls': case 'xlsx': return '<i class="fa-solid fa-file-excel" style="color:#27ae60;"></i>';
        case 'ppt': case 'pptx': return '<i class="fa-solid fa-file-powerpoint" style="color:#f39c12;"></i>';
        default: return '<i class="fa-solid fa-file" style="color:#7f8c8d;"></i>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Download Files - EduWide</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
<?php include_once("includes/css-links-inc.php"); ?>
<style>
/* Reset & Base */
body { font-family: 'Nunito', sans-serif; background: #f5f6fa; color: #2c3e50; margin:0; padding:0; }
a { text-decoration:none; color:inherit; }

/* Page Title */
.pagetitle h1 { font-weight: 800; font-size: 28px; color: #2d3436; margin-bottom:5px; }
.breadcrumb a { color:#636e72; }
.breadcrumb-item.active { color:#0984e3; }

/* Form */
.form-select { border-radius: 8px; padding: 8px 12px; font-size: 14px; }

/* File Grid */
.file-grid { display:grid; grid-template-columns: repeat(auto-fill,minmax(220px,1fr)); gap:20px; margin-top:20px; }
.file-card { background:#fff; border-radius:15px; padding:20px; text-align:center; box-shadow:0 4px 15px rgba(0,0,0,0.08); transition:0.3s; position:relative; overflow:hidden; }
.file-card:hover { transform:translateY(-8px); box-shadow:0 12px 25px rgba(0,0,0,0.15); }
.file-icon { font-size:70px; margin-bottom:15px; }
.file-title { font-weight:600; font-size:16px; margin-bottom:6px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.file-meta { font-size:12px; color:#636e72; margin-bottom:4px; }
.download-btn { display:inline-block; padding:8px 16px; font-size:13px; font-weight:600; border-radius:8px; background:linear-gradient(90deg,#0984e3,#6c5ce7); color:#fff; transition:0.3s; }
.download-btn:hover { opacity:0.8; }

/* Subject Heading */
.subject-heading { font-size:20px; font-weight:700; color:#6c5ce7; border-bottom:2px solid #dcdde1; padding-bottom:5px; margin-bottom:12px; }

/* Responsive */
@media (min-width: 1200px) { .file-grid { grid-template-columns: repeat(5,1fr); } }
@media (max-width: 768px) { .file-card { padding:15px; } .file-icon { font-size:60px; } }
</style>
</head>
<body>
<?php include_once("includes/header.php"); ?>
<?php include_once("includes/student-sidebar.php"); ?>

<main id="main" class="main">
<div class="pagetitle">
    <h1>Download Files</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Download Files</li>
        </ol>
    </nav>
</div>

<section class="section">
    <div class="card">
        <div class="card-body">

            <!-- Semester & Subject Selection -->
            <form method="GET" class="mb-4 row g-3">
                <div class="col-md-4">
                    <label for="semester" class="form-label">Semester:</label>
                    <select id="semester" name="semester" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Select Semester --</option>
                        <?php foreach ($semesters as $sem): ?>
                        <option value="<?= htmlspecialchars($sem) ?>" <?= ($sem == $selectedSemester) ? 'selected' : '' ?>>Semester <?= htmlspecialchars($sem) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($selectedSemester !== ''): 
                    $subjects = [];
                    $subjectQuery = $conn->prepare("SELECT id, name FROM subjects WHERE semester = ? ORDER BY name ASC");
                    if ($subjectQuery) {
                        $subjectQuery->bind_param("s", $selectedSemester);
                        $subjectQuery->execute();
                        $subjectResult = $subjectQuery->get_result();
                        while ($row = $subjectResult->fetch_assoc()) $subjects[] = $row;
                        $subjectQuery->close();
                    }
                ?>
                <div class="col-md-4">
                    <label for="subject" class="form-label">Subject:</label>
                    <select id="subject" name="subject" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Select Subject --</option>
                        <?php foreach ($subjects as $subject): ?>
                        <option value="<?= htmlspecialchars($subject['id']) ?>" <?= ($subject['id'] == $selectedSubject) ? 'selected' : '' ?>><?= htmlspecialchars($subject['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </form>

            <!-- Files Display -->
            <?php if ($selectedSemester !== '' && $selectedSubject !== ''): 
                $stmt = $conn->prepare("SELECT name FROM subjects WHERE id = ?");
                $subjectName = 'Unknown Subject';
                if ($stmt) {
                    $stmt->bind_param("i", $selectedSubject);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    if ($row = $res->fetch_assoc()) $subjectName = $row['name'];
                    $stmt->close();
                }
                echo "<h5 class='subject-heading'>" . htmlspecialchars($subjectName) . "</h5>";

                $stmt = $conn->prepare("SELECT * FROM tuition_files WHERE subject_id = ? AND status = 'active' ORDER BY uploaded_at DESC");
                $notesByWeek = []; $otherFiles = [];
                if ($stmt) {
                    $stmt->bind_param("i", $selectedSubject);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($file = $result->fetch_assoc()) {
                        if (strtolower($file['category']) === 'notes' && preg_match('/^Week\s*(\d+)/i', $file['title'], $matches))
                            $notesByWeek[(int)$matches[1]][] = $file;
                        else $otherFiles[] = $file;
                    }
                    $stmt->close();
                }
                ksort($notesByWeek);
            ?>

            <?php if (!empty($notesByWeek) || !empty($otherFiles)): ?>
                <?php foreach ($notesByWeek as $weekNum => $files): ?>
                    <h5 class="mt-4">Week <?= htmlspecialchars($weekNum) ?></h5>
                    <div class="file-grid">
                        <?php foreach ($files as $f):
                            $ext = pathinfo($f['filename'], PATHINFO_EXTENSION); ?>
                        <div class="file-card">
                            <div class="file-icon"><?= getFileIconColored($ext) ?></div>
                            <div class="file-title"><?= htmlspecialchars($f['title']) ?></div>
                            <div class="file-meta"><?= htmlspecialchars($f['category']) ?></div>
                            <div class="file-meta">By: <?= htmlspecialchars($f['uploaded_by_name']) ?> (<?= htmlspecialchars($f['uploaded_by_role']) ?>)</div>
                            <div class="file-meta"><?= htmlspecialchars($f['uploaded_at']) ?></div>
                            <a class="download-btn" href="uploads/<?= urlencode($f['filename']) ?>" target="_blank" rel="noopener">Download</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>

                <?php if (!empty($otherFiles)): ?>
                    <h5 class="mt-4">Other Files</h5>
                    <div class="file-grid">
                        <?php foreach ($otherFiles as $f):
                            $ext = pathinfo($f['filename'], PATHINFO_EXTENSION); ?>
                        <div class="file-card">
                            <div class="file-icon"><?= getFileIconColored($ext) ?></div>
                            <div class="file-title"><?= htmlspecialchars($f['title']) ?></div>
                            <div class="file-meta"><?= htmlspecialchars($f['category']) ?></div>
                            <div class="file-meta">By: <?= htmlspecialchars($f['uploaded_by_name']) ?> (<?= htmlspecialchars($f['uploaded_by_role']) ?>)</div>
                            <div class="file-meta"><?= htmlspecialchars($f['uploaded_at']) ?></div>
                            <a class="download-btn" href="uploads/<?= urlencode($f['filename']) ?>" target="_blank" rel="noopener">Download</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <p class="text-center mt-3">No files found for this subject.</p>
            <?php endif; ?>
            <?php elseif ($selectedSemester !== ''): ?>
                <p class="text-center mt-3">Please select a subject to view files.</p>
            <?php else: ?>
                <p class="text-center mt-3">Please select a semester to view available files.</p>
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

<?php
session_start();
require_once '../includes/db-conn.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$user_id = $_SESSION['admin_id'] ?? null;
if ($user_id) {
    $stmt = $conn->prepare("SELECT name, email, nic, mobile, profile_picture FROM admins WHERE id = ?");
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
        case 'jpg': case 'jpeg': case 'png': case 'gif': return '<i class="fa-solid fa-file-image" style="color:#9b59b6;"></i>';
        case 'zip': case 'rar': return '<i class="fa-solid fa-file-zipper" style="color:#f1c40f;"></i>';
        default: return '<i class="fa-solid fa-file" style="color:#7f8c8d;"></i>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Download Files - EduWide</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
<?php include_once("../includes/css-links-inc.php"); ?>
<style>
/* Modern Reset */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Nunito', sans-serif;
}

/* Color Scheme */
:root {
  --primary: #4361ee;
  --secondary: #3f37c9;
  --accent: #4895ef;
  --success: #4cc9f0;
  --light: #f8f9fa;
  --gray: #6c757d;
  --light-gray: #e9ecef;
  --card-shadow: 0 10px 20px rgba(0,0,0,0.05), 0 6px 6px rgba(0,0,0,0.05);
  --card-hover-shadow: 0 15px 30px rgba(0,0,0,0.1), 0 10px 10px rgba(0,0,0,0.08);
}

/* Base Styles */
body {
  background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
  color: var(--dark);
  line-height: 1.6;
  min-height: 100vh;
}

/* Layout */
.main-container {
  display: flex;
  min-height: 100vh;
}

/* He
/* Sidebar */
.sidebar {
  background: white;
  box-shadow: 4px 0 15px rgba(0,0,0,0.05);
  z-index: 999;
}

/* Main Content */
#main {
  flex: 1;
  padding: 20px;
  padding-top: 20px;
  transition: all 0.3s ease;
}

/* Page Title */
.pagetitle {
  position: relative;
}


.breadcrumb {
  background: none;
  padding: 0;
  margin-bottom: 0;
}

.breadcrumb-item a {
  color: var(--primary);
  text-decoration: none;
  transition: all 0.2s ease;
}

.breadcrumb-item a:hover {
  color: var(--secondary);
  text-decoration: underline;
}

.breadcrumb-item.active {
  color: var(--gray);
}

/* Card Styles */
.card {
  border: none;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: var(--card-shadow);
  transition: all 0.3s ease;
  background: rgba(255, 255, 255, 0.95);
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: var(--card-hover-shadow);
}

.card-body {
  padding: 2rem;
}

/* Form Styles */
.filter-form {
  background: white;
  border-radius: 14px;
  padding: 1.5rem;
  box-shadow: 0 5px 15px rgba(0,0,0,0.05);
  margin-bottom: 2rem;
}

.form-label {
  font-weight: 600;
  color: var(--secondary);
  margin-bottom: 0.5rem;
}

.form-select {
  border: 2px solid var(--light-gray);
  border-radius: 12px;
  padding: 12px 16px;
  font-size: 1rem;
  height: auto;
  transition: all 0.3s ease;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
  background-position: right 1rem center;
  background-size: 16px 12px;
}

.form-select:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
}

/* Subject Heading */
.subject-heading {
  font-size: 1.8rem;
  font-weight: 700;
  color: var(--secondary);
  position: relative;
  padding-bottom: 0.8rem;
  margin: 2rem 0 1.5rem;
  display: flex;
  align-items: center;
}

.subject-heading:after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 80px;
  height: 4px;
  background: linear-gradient(90deg, var(--accent), var(--success));
  border-radius: 2px;
}

.subject-heading i {
  margin-right: 12px;
  font-size: 1.6rem;
  color: var(--accent);
}

/* File Grid */
.file-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 25px;
  margin-top: 20px;
}

.file-card {
  background: white;
  border-radius: 16px;
  padding: 25px;
  text-align: center;
  box-shadow: var(--card-shadow);
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.file-card:hover {
  transform: translateY(-10px);
  box-shadow: var(--card-hover-shadow);
}

.file-card:before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 4px;
  background: linear-gradient(90deg, var(--accent), var(--success));
}

.file-icon {
  font-size: 3.5rem;
  margin-bottom: 20px;
  color: var(--primary);
  transition: all 0.3s ease;
}

.file-card:hover .file-icon {
  transform: scale(1.1);
}

.file-title {
  font-weight: 700;
  font-size: 1.1rem;
  margin-bottom: 10px;
  color: var(--dark);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  min-height: 52px;
}

.file-meta {
  font-size: 0.85rem;
  color: var(--gray);
  margin-bottom: 5px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
}

.file-meta i {
  font-size: 0.9rem;
  color: var(--accent);
}

.file-uploader {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  margin-top: auto;
  padding-top: 10px;
}

.uploader-badge {
  background: rgba(72, 149, 239, 0.15);
  color: var(--accent);
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.download-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 24px;
  font-size: 1rem;
  font-weight: 600;
  border-radius: 12px;
  background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
  color: white;
  transition: all 0.3s ease;
  border: none;
  width: 100%;
  margin-top: 20px;
  box-shadow: 0 4px 12px rgba(67, 97, 238, 0.25);
}

.download-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 16px rgba(67, 97, 238, 0.35);
  color: white;
}

.download-btn:active {
  transform: translateY(0);
}

/* Week Section */
.week-section {
  margin-bottom: 2.5rem;
  position: relative;
}

.week-header {
  display: flex;
  align-items: center;
  padding: 12px 20px;
  background: linear-gradient(135deg, var(--accent) 0%, var(--success) 100%);
  color: white;
  border-radius: 12px;
  margin-bottom: 20px;
  box-shadow: 0 4px 12px rgba(72, 149, 239, 0.3);
}

.week-header h5 {
  font-weight: 700;
  margin: 0;
  font-size: 1.3rem;
  display: flex;
  align-items: center;
  gap: 10px;
}

.week-header i {
  font-size: 1rem;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 3rem;
  background: white;
  border-radius: 16px;
  box-shadow: var(--card-shadow);
}

.empty-state i {
  font-size: 4rem;
  color: var(--light-gray);
  margin-bottom: 1.5rem;
}

.empty-state h4 {
  color: var(--gray);
  font-weight: 600;
  margin-bottom: 1rem;
}

.empty-state p {
  color: var(--gray);
  max-width: 500px;
  margin: 0 auto;
}

/* Responsive */
@media (max-width: 992px) {
  .file-grid {
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  }
}

@media (max-width: 768px) {
  .card-body {
    padding: 1.5rem;
  }
  
  .filter-form {
    padding: 1.2rem;
  }
  
  .file-card {
    padding: 10px;
  }
  
  .pagetitle h1 {
    font-size: 1.8rem;
  }
}

@media (max-width: 576px) {
  .file-grid {
    grid-template-columns: 1fr;
  }
  
  .subject-heading {
    font-size: 1rem;
  }
  
  .week-header h5 {
    font-size: 1rem;
  }
  
  .empty-state {
    padding: 2rem 1.5rem;
  }
}

/* Animation */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.file-card {
  animation: fadeIn 0.5s ease forwards;
  opacity: 0;
}

/* Hover effect for form elements */
.form-select:hover {
  border-color: #b8c2cc;
}
</style>
</head>
<body>
<?php include_once("../includes/header.php"); ?>
<?php include_once("../includes/admin-sidebar.php"); ?>

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
            <div class="filter-form">
                <form method="GET" class="row g-4">
                    <div class="col-lg-4 col-md-6">
                        <label for="semester" class="form-label"><i class="fas fa-layer-group me-2"></i>Semester</label>
                        <select id="semester" name="semester" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Select Semester --</option>
                            <?php foreach ($semesters as $sem): ?>
                            <option value="<?= htmlspecialchars($sem) ?>" <?= ($sem == $selectedSemester) ? 'selected' : '' ?>>
                                Semester <?= htmlspecialchars($sem) ?>
                            </option>
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
                    <div class="col-lg-4 col-md-6">
                        <label for="subject" class="form-label"><i class="fas fa-book me-2"></i>Subject</label>
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
                    
                    <?php if ($selectedSemester !== '' && $selectedSubject !== ''): ?>
                    <div class="col-lg-4 col-md-12 d-flex align-items-end">
                    </div>
                    <?php endif; ?>
                </form>
            </div>

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
                
                echo "<h5 class='subject-heading'><i class='fas fa-book-open'></i>" . htmlspecialchars($subjectName) . "</h5>";

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
                    <div class="week-section">
                        <div class="week-header">
                            <h5><i class="fas fa-calendar-week"></i> Week <?= htmlspecialchars($weekNum) ?></h5>
                        </div>
                        <div class="file-grid">
                            <?php foreach ($files as $index => $f):
                                $ext = pathinfo($f['filename'], PATHINFO_EXTENSION); ?>
                            <div class="file-card" style="animation-delay: <?= $index * 0.05 ?>s">
                                <div class="file-icon"><?= getFileIconColored($ext) ?></div>
                                <div class="file-title"><?= htmlspecialchars($f['title']) ?></div>
                                <div class="file-meta">
                                    <i class="fas fa-tag"></i><?= htmlspecialchars($f['category']) ?>
                                </div>
                                <div class="file-meta">
                                    <i class="fas fa-clock"></i><?= htmlspecialchars(date('M d, Y', strtotime($f['uploaded_at']))) ?>
                                </div>
                                <div class="file-uploader">
                                    <span class="uploader-badge"><?= htmlspecialchars($f['uploaded_by_role']) ?></span>
                                    <span><?= htmlspecialchars($f['uploaded_by_name']) ?></span>
                                </div>
                                <a class="download-btn" href="uploads/<?= urlencode($f['filename']) ?>" target="_blank" rel="noopener">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (!empty($otherFiles)): ?>
                    <div class="week-section">
                        <div class="week-header">
                            <h5><i class="fas fa-folder-open"></i> Additional Resources</h5>
                        </div>
                        <div class="file-grid">
                            <?php foreach ($otherFiles as $index => $f):
                                $ext = pathinfo($f['filename'], PATHINFO_EXTENSION); ?>
                            <div class="file-card" style="animation-delay: <?= $index * 0.05 ?>s">
                                <div class="file-icon"><?= getFileIconColored($ext) ?></div>
                                <div class="file-title"><?= htmlspecialchars($f['title']) ?></div>
                                <div class="file-meta">
                                    <i class="fas fa-tag"></i><?= htmlspecialchars($f['category']) ?>
                                </div>
                                <div class="file-meta">
                                    <i class="fas fa-clock"></i><?= htmlspecialchars(date('M d, Y', strtotime($f['uploaded_at']))) ?>
                                </div>
                                <div class="file-uploader">
                                    <span class="uploader-badge"><?= htmlspecialchars($f['uploaded_by_role']) ?></span>
                                    <span><?= htmlspecialchars($f['uploaded_by_name']) ?></span>
                                </div>
                                <a class="download-btn" href="uploads/<?= urlencode($f['filename']) ?>" target="_blank" rel="noopener">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h4>No Files Available Yet</h4>
                    <p>There are currently no files for this subject. Please check back later or contact your instructor for more information.</p>
                </div>
            <?php endif; ?>
            <?php elseif ($selectedSemester !== ''): ?>
                <div class="empty-state">
                    <i class="fas fa-book"></i>
                    <h4>Select a Subject</h4>
                    <p>Please select a subject from the dropdown to view available files for this semester.</p>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-graduation-cap"></i>
                    <h4>Select a Semester</h4>
                    <p>Choose a semester from the dropdown to view available subjects and files.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</section>
</main>

<?php include_once("../includes/footer2.php") ?>
<?php include_once("../includes/js-links-inc.php") ?>

<script>
// Scroll to files section
function scrollToFiles() {
    const filesSection = document.querySelector('.subject-heading');
    if (filesSection) {
        filesSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Animation on card hover
document.querySelectorAll('.file-card').forEach(card => {
    card.addEventListener('mouseenter', () => {
        card.style.transform = 'translateY(-10px)';
    });
    
    card.addEventListener('mouseleave', () => {
        card.style.transform = 'translateY(0)';
    });
});

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
</body>
</html>
<?php $conn->close(); ?>
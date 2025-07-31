<?php
session_start();
require_once '../includes/db-conn.php';

if (!isset($_SESSION['sadmin_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['sadmin_id'];
$sql = "SELECT name, email, nic, mobile, profile_picture FROM sadmins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$semesters = [];
$semQuery = $conn->query("SELECT DISTINCT semester FROM subjects ORDER BY semester ASC");
while ($row = $semQuery->fetch_assoc()) {
    $semesters[] = $row['semester'];
}

$selectedSemester = $_GET['semester'] ?? '';
$selectedSubjectId = $_GET['subject'] ?? '';

$subjects = [];
if ($selectedSemester !== '') {
    $subjectQuery = $conn->prepare("SELECT id, name FROM subjects WHERE semester = ? ORDER BY name ASC");
    $subjectQuery->bind_param("s", $selectedSemester);
    $subjectQuery->execute();
    $resultSubjects = $subjectQuery->get_result();
    while ($row = $resultSubjects->fetch_assoc()) {
        $subjects[] = $row;
    }
    $subjectQuery->close();
}

// Function for colored file icons by extension
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
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Manage Resources - EduWide</title>
    <?php include_once("../includes/css-links-inc.php"); ?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        .week-subheading {
            font-weight: 600;
            color: #1a73e8;
            margin-top: 20px;
            margin-left: 15px;
        }
    </style>
</head>
<body>
<?php include_once("../includes/header.php") ?>
<?php include_once("../includes/sadmin-sidebar.php") ?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Manage Resources</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Manage Resources</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <h5 class="card-title">Select Semester & Subject</h5>
                        <form method="GET" class="mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <select name="semester" class="form-select" onchange="this.form.submit()">
                                        <option value="">-- Select Semester --</option>
                                        <?php foreach ($semesters as $sem): ?>
                                            <option value="<?= htmlspecialchars($sem) ?>" <?= $sem == $selectedSemester ? 'selected' : '' ?>>
                                                Semester <?= htmlspecialchars($sem) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php if (!empty($subjects)): ?>
                                    <div class="col-md-4">
                                        <select name="subject" class="form-select" onchange="this.form.submit()">
                                            <option value="">-- All Subjects --</option>
                                            <?php foreach ($subjects as $subject): ?>
                                                <option value="<?= htmlspecialchars($subject['id']) ?>" <?= $selectedSubjectId == $subject['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($subject['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </form>

                        <h5 class="card-title">File Upload</h5>
                        <?php if ($selectedSemester !== ''): ?>
                            <form id="multiFileForm" class="mb-4" enctype="multipart/form-data">
                                <input type="hidden" name="semester" value="<?= htmlspecialchars($selectedSemester) ?>">
                                <div id="upload-sections">
                                    <div class="upload-section row g-3 mb-3">
                                        <div class="col-md-3">
                                            <input type="text" name="title[]" class="form-control" placeholder="File Title" required>
                                        </div>
                                        <div class="col-md-3">
                                            <select name="subject_id[]" class="form-select" required>
                                                <option value="">Select Subject</option>
                                                <?php foreach ($subjects as $subject): ?>
                                                    <option value="<?= htmlspecialchars($subject['id']) ?>"><?= htmlspecialchars($subject['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <select name="category[]" class="form-select" required>
                                                <option value="">Category</option>
                                                <option value="Pass Papers">Pass Papers</option>
                                                <option value="Model Paper">Model Papers</option>
                                                <option value="Notes">Notes </option>
                                                <option value="Lecturer Notes">Notes - Lecturer Upload</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="file" name="file[]" class="form-control" required>
                                        </div>
                                        <div class="col-md-1 d-flex align-items-center">
                                            <button type="button" class="btn btn-danger btn-sm remove-section">X</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <button type="button" class="btn btn-secondary btn-sm" id="addSectionBtn">+ Add More</button>
                                    <button type="submit" class="btn btn-primary float-end">Upload All Files</button>
                                </div>
                                <div class="progress mt-3" style="height: 25px; display: none;" id="uploadProgressBar">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                         style="width: 0%;" id="progressBarText">0%
                                    </div>
                                </div>
                            </form>

                            <script>
                                document.getElementById("addSectionBtn").addEventListener("click", function () {
                                    const section = document.querySelector(".upload-section");
                                    const clone = section.cloneNode(true);
                                    clone.querySelectorAll("input, select").forEach(el => el.value = "");
                                    document.getElementById("upload-sections").appendChild(clone);
                                });

                                document.addEventListener("click", function (e) {
                                    if (e.target.classList.contains("remove-section")) {
                                        const total = document.querySelectorAll(".upload-section").length;
                                        if (total > 1) {
                                            e.target.closest(".upload-section").remove();
                                        }
                                    }
                                });

                                document.getElementById("multiFileForm").addEventListener("submit", function (e) {
                                    e.preventDefault();

                                    const form = e.target;
                                    const formData = new FormData(form);
                                    const xhr = new XMLHttpRequest();

                                    const progressContainer = document.getElementById("uploadProgressBar");
                                    const progressBar = document.getElementById("progressBarText");

                                    progressContainer.style.display = "block";
                                    progressBar.style.width = "0%";
                                    progressBar.innerText = "0%";

                                    xhr.upload.addEventListener("progress", function (e) {
                                        if (e.lengthComputable) {
                                            const percent = Math.round((e.loaded / e.total) * 100);
                                            progressBar.style.width = percent + "%";
                                            progressBar.innerText = percent + "%";
                                        }
                                    });

                                    xhr.addEventListener("load", function () {
                                        if (xhr.status === 200) {
                                            progressBar.classList.remove("bg-danger");
                                            progressBar.classList.add("bg-success");
                                            progressBar.innerText = "Upload Complete";
                                            setTimeout(() => {
                                                window.location.reload();
                                            }, 1000);
                                        } else {
                                            progressBar.classList.remove("bg-success");
                                            progressBar.classList.add("bg-danger");
                                            progressBar.innerText = "Upload Failed";
                                        }
                                    });

                                    xhr.open("POST", "upload-file.php");
                                    xhr.send(formData);
                                });
                            </script>

                            <h5 class="card-title mt-4">Uploaded Files</h5>

                            <?php
                            // Fetch subjects for this semester
                            $subjectGroupQuery = $conn->prepare("
                                SELECT DISTINCT s.id, s.name 
                                FROM tuition_files tf 
                                JOIN subjects s ON tf.subject_id = s.id 
                                WHERE s.semester = ? 
                                ORDER BY s.name ASC
                            ");
                            $subjectGroupQuery->bind_param("s", $selectedSemester);
                            $subjectGroupQuery->execute();
                            $subjectResult = $subjectGroupQuery->get_result();

                            while ($subject = $subjectResult->fetch_assoc()):
                                // If a specific subject is selected, skip others
                                if ($selectedSubjectId && $selectedSubjectId != $subject['id']) {
                                    continue;
                                }
                                ?>
                                <h5 class="mt-4 text-primary"><?= htmlspecialchars($subject['name']) ?></h5>

                                <?php
                                // Fetch files for this subject, grouped by Notes Weeks and Others
                                $stmtFiles = $conn->prepare("SELECT * FROM tuition_files WHERE subject_id = ? ORDER BY uploaded_at DESC");
                                $notesByWeek = [];
                                $otherFiles = [];

                                if ($stmtFiles) {
                                    $stmtFiles->bind_param("i", $subject['id']);
                                    $stmtFiles->execute();
                                    $resultFiles = $stmtFiles->get_result();

                                    while ($file = $resultFiles->fetch_assoc()) {
                                        if (strtolower($file['category']) === 'notes' && preg_match('/^(Week\s*\d+)/i', $file['title'], $matches)) {
                                            $weekKey = $matches[1]; // e.g. "Week 1"
                                            $notesByWeek[$weekKey][] = $file;
                                        } else {
                                            $otherFiles[] = $file;
                                        }
                                    }
                                    $stmtFiles->close();
                                }

                                // Sort weeks naturally
                                if (!empty($notesByWeek)) {
                                    ksort($notesByWeek, SORT_NATURAL | SORT_FLAG_CASE);
                                }

                                // Display Notes grouped by week
                                foreach ($notesByWeek as $week => $files):
                                    ?>
                                    <h6 class="week-subheading"><?= htmlspecialchars($week) ?></h6>
                                    <table class="table table-bordered mb-4">
                                        <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Extension</th>
                                            <th>Uploaded_at</th>
                                            <th>File</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($files as $f): 
                                            $ext = pathinfo($f['filename'], PATHINFO_EXTENSION);
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($f['title']) ?></td>
                                                <td><?= htmlspecialchars($f['category']) ?></td>
                                                <td><?= getFileIconColored($ext) . ' ' . strtoupper(htmlspecialchars($ext)) ?></td>
                                                 <td><?= htmlspecialchars($f['uploaded_at']) ?></td>
                                                <td><a href="../uploads/<?= rawurlencode($f['filename']) ?>" target="_blank" rel="noopener">View</a></td>
                                                <td><span class="badge bg-<?= $f['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                        <?= ucfirst(htmlspecialchars($f['status'])) ?>
                                                    </span></td>
                                                <td>
                                                    <?php if ($f['status'] !== 'active'): ?>
                                                        <a href="change-file-status.php?id=<?= $f['id'] ?>&status=active&semester=<?= urlencode($selectedSemester) ?>&subject=<?= urlencode($selectedSubjectId) ?>" class="btn btn-sm btn-success">Activate</a>
                                                    <?php endif; ?>
                                                    <?php if ($f['status'] !== 'inactive'): ?>
                                                        <a href="change-file-status.php?id=<?= $f['id'] ?>&status=inactive&semester=<?= urlencode($selectedSemester) ?>&subject=<?= urlencode($selectedSubjectId) ?>" class="btn btn-sm btn-secondary">Disable</a>
                                                    <?php endif; ?>
                                                    <a href="delete-file.php?id=<?= $f['id'] ?>&semester=<?= urlencode($selectedSemester) ?>&subject=<?= urlencode($selectedSubjectId) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this file?')">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endforeach; ?>

                                <?php if (!empty($otherFiles)): ?>
                                    <h6 class="week-subheading">Other Files</h6>
                                    <table class="table table-bordered mb-4">
                                        <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Extension</th>
                                            <th>Uploaded_at</th>
                                            <th>File</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($otherFiles as $f):
                                            $ext = pathinfo($f['filename'], PATHINFO_EXTENSION);
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($f['title']) ?></td>
                                                <td><?= htmlspecialchars($f['category']) ?></td>
                                                <td><?= getFileIconColored($ext) . ' ' . strtoupper(htmlspecialchars($ext)) ?></td>
                                                <td><?= htmlspecialchars($f['uploaded_at']) ?></td>
                                                <td><a href="../uploads/<?= rawurlencode($f['filename']) ?>" target="_blank" rel="noopener">View</a></td>
                                                <td><span class="badge bg-<?= $f['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                        <?= ucfirst(htmlspecialchars($f['status'])) ?>
                                                    </span></td>
                                                <td>
                                                    <?php if ($f['status'] !== 'active'): ?>
                                                        <a href="change-file-status.php?id=<?= $f['id'] ?>&status=active&semester=<?= urlencode($selectedSemester) ?>&subject=<?= urlencode($selectedSubjectId) ?>" class="btn btn-sm btn-success">Activate</a>
                                                    <?php endif; ?>
                                                    <?php if ($f['status'] !== 'inactive'): ?>
                                                        <a href="change-file-status.php?id=<?= $f['id'] ?>&status=inactive&semester=<?= urlencode($selectedSemester) ?>&subject=<?= urlencode($selectedSubjectId) ?>" class="btn btn-sm btn-secondary">Disable</a>
                                                    <?php endif; ?>
                                                    <a href="delete-file.php?id=<?= $f['id'] ?>&semester=<?= urlencode($selectedSemester) ?>&subject=<?= urlencode($selectedSubjectId) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this file?')">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>

                            <?php endwhile; $subjectGroupQuery->close(); ?>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include_once("../includes/footer.php") ?>
<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
<?php include_once("../includes/js-links-inc.php") ?>
</body>
</html>
<?php $conn->close(); ?>

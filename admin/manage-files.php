<?php
// manage-files.php
session_start();
require_once '../includes/db-conn.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['admin_id'];
$sql = "SELECT name, email, nic, mobile, profile_picture FROM admins WHERE id = ?";
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Manage Resources - EduWide</title>
    <?php include_once("../includes/css-links-inc.php"); ?>
</head>
<body>
<?php include_once("../includes/header.php") ?>
<?php include_once("../includes/admin-sidebar.php") ?>
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
                                            <option value="<?= $sem ?>" <?= $sem == $selectedSemester ? 'selected' : '' ?>>
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
                                                <option value="<?= $subject['id'] ?>" <?= $selectedSubjectId == $subject['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($subject['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </form>

                        <?php if ($selectedSemester !== ''): ?>
                            <h5 class="card-title">File Upload</h5>
                            <form action="upload-file.php" method="POST" enctype="multipart/form-data" class="mb-4" id="multiFileForm">
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
                                                    <option value="<?= $subject['id'] ?>"><?= htmlspecialchars($subject['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <select name="category[]" class="form-select" required>
                                                <option value="">Category</option>
                                                <option value="Pass Papers">Pass Papers</option>
                                                <option value="Notes">Notes</option>
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
                            </script>
                            <h5 class="card-title mt-4">Uploaded Files</h5>
                            <?php
                            $subjectGroupQuery = $conn->prepare("SELECT DISTINCT s.id, s.name FROM tuition_files tf JOIN subjects s ON tf.subject_id = s.id WHERE s.semester = ? ORDER BY s.name ASC");
                            $subjectGroupQuery->bind_param("s", $selectedSemester);
                            $subjectGroupQuery->execute();
                            $subjectResult = $subjectGroupQuery->get_result();
                            while ($subject = $subjectResult->fetch_assoc()):
                                if ($selectedSubjectId && $selectedSubjectId != $subject['id']) continue;
                            ?>
                                <h5 class="mt-4 text-primary"><?= htmlspecialchars($subject['name']) ?></h5>
                                <table class="table table-bordered mb-4">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>File</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $fileQuery = $conn->query("SELECT * FROM tuition_files WHERE subject_id = " . intval($subject['id']) . " ORDER BY uploaded_at DESC");
                                        while ($row = $fileQuery->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['title']) ?></td>
                                            <td><?= htmlspecialchars($row['category']) ?></td>
                                            <td><a href="../uploads/<?= $row['filename'] ?>" target="_blank">View</a></td>
                                            <td><span class="badge bg-<?= $row['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                <?= ucfirst($row['status']) ?>
                                            </span></td>
                                            <td>
                                                <?php if ($row['status'] !== 'active'): ?>
                                                    <a href="change-file-status.php?id=<?= $row['id'] ?>&status=active&semester=<?= urlencode($selectedSemester) ?>" class="btn btn-sm btn-success">Activate</a>
                                                <?php endif; ?>
                                                <?php if ($row['status'] !== 'inactive'): ?>
                                                    <a href="change-file-status.php?id=<?= $row['id'] ?>&status=inactive&semester=<?= urlencode($selectedSemester) ?>" class="btn btn-sm btn-secondary">Disable</a>
                                                <?php endif; ?>
                                                <a href="delete-file.php?id=<?= $row['id'] ?>&semester=<?= urlencode($selectedSemester) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this file?')">Delete</a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
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
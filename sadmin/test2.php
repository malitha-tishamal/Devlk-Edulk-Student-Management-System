<?php
session_start();
require_once '../includes/db-conn.php';

// Fetch semesters for filter dropdown
// Fetch user details
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

$selectedSemester = isset($_GET['semester']) ? $_GET['semester'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>View Files - EduWide</title>
    <?php include_once("../includes/css-links-inc.php"); ?>
</head>
<body>
<?php include_once("../includes/header.php") ?>
<?php include_once("../includes/sadmin-sidebar.php") ?>
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
                <form method="GET" class="mb-4">
                    <label for="semester">Select Semester:</label>
                    <select id="semester" name="semester" onchange="this.form.submit()" class="form-select" style="max-width:300px;">
                        <option value="">-- All Semesters --</option>
                        <?php foreach ($semesters as $sem): ?>
                            <option value="<?= $sem ?>" <?= $sem == $selectedSemester ? 'selected' : '' ?>>Semester <?= htmlspecialchars($sem) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <?php if ($selectedSemester !== ''): ?>
                    <?php
                    // Fetch subjects of selected semester
                    $subjectStmt = $conn->prepare("SELECT id, name FROM subjects WHERE semester = ? ORDER BY name ASC");
                    $subjectStmt->bind_param("s", $selectedSemester);
                    $subjectStmt->execute();
                    $subjectResult = $subjectStmt->get_result();
                    ?>

                    <?php while ($subject = $subjectResult->fetch_assoc()): ?>
                        <h5 class="mt-4"><?= htmlspecialchars($subject['name']) ?></h5>
                        <table class="table table-bordered mb-4">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>File</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Show only active files
                                $fileStmt = $conn->prepare("SELECT title, category, filename FROM tuition_files WHERE subject_id = ? AND status = 'active' ORDER BY uploaded_at DESC");
                                $fileStmt->bind_param("i", $subject['id']);
                                $fileStmt->execute();
                                $fileResult = $fileStmt->get_result();

                                if ($fileResult->num_rows === 0) {
                                    echo '<tr><td colspan="3">No files available.</td></tr>';
                                } else {
                                    while ($file = $fileResult->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($file['title']) ?></td>
                                        <td><?= htmlspecialchars($file['category']) ?></td>
                                        <td><a href="../uploads/<?= urlencode($file['filename']) ?>" target="_blank" rel="noopener">Download</a></td>
                                    </tr>
                                <?php
                                    endwhile;
                                }
                                $fileStmt->close();
                                ?>
                            </tbody>
                        </table>
                    <?php endwhile; ?>
                    <?php $subjectStmt->close(); ?>
                <?php else: ?>
                    <p>Please select a semester to view available files.</p>
                <?php endif; ?>

            </div>
        </div>
    </section>
</main>

<?php include_once("../includes/footer.php") ?>
<?php include_once("../includes/js-links-inc.php") ?>
</body>
</html>

<?php $conn->close(); ?>

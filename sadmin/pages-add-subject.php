<?php
session_start();
require_once '../includes/db-conn.php';

// Redirect if not logged in
if (!isset($_SESSION['sadmin_id'])) {
    header("Location: ../index.php");
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = trim($_POST['code']);
    $name = trim($_POST['name']);
    $semester = trim($_POST['semester']);
    $description = trim($_POST['description']);

    if (!empty($code) && !empty($name) && !empty($semester) && !empty($description)) {
        $insert_sql = "INSERT INTO subjects (code, name, semester, description) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ssss", $code, $name, $semester, $description);

        if ($stmt->execute()) {
            header("Location: pages-courses.php?msg=added");
            exit;
        } else {
            $error = "Error: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Add Subject - Edulk</title>
    <?php include_once("../includes/css-links-inc.php"); ?>
</head>

<body>

<?php include_once("../includes/header.php"); ?>
<?php include_once("../includes/sadmin-sidebar.php"); ?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Add Subject</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="">Home</a></li>
                <li class="breadcrumb-item"><a href="">Subjects</a></li>
                <li class="breadcrumb-item active">Add Subject</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <?php if (!empty($error)) { ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php } ?>

                        <form action="" method="post" class="mt-3">

                            <div class="mb-3">
                                <label for="code" class="form-label">Subject Code</label>
                                <input type="text" class="form-control  w-50" id="code" name="code" required>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">Subject Name</label>
                                <input type="text" class="form-control w-50" id="name" name="name" required>
                            </div>

                            <div class="mb-3">
                                <label for="semester" class="form-label">Semester</label>
                                <input type="text" class="form-control w-50" id="semester" name="semester" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Subject Description</label>
                                <textarea class="form-control w-50" id="description" name="description" rows="3" required></textarea>
                            </div>

                            <button type="submit" class="btn btn-success">Add Subject</button>
                            <a href="pages-courses.php" class="btn btn-danger">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include_once("../includes/footer.php"); ?>
<a href="#" class="back-to-top d-flex align-items-center justify-content-center">
    <i class="bi bi-arrow-up-short"></i>
</a>
<?php include_once("../includes/js-links-inc.php"); ?>
</body>
</html>

<?php $conn->close(); ?>

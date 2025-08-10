<?php
session_start();
require_once '../includes/db-conn.php';

// Redirect if not logged in
if (!isset($_SESSION['lecture_id'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['lecture_id'];
$sql = "SELECT * FROM lectures WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$subject_id = $_GET['subject_id'];

// Fetch the subject details
$sql = "SELECT * FROM subjects WHERE id = $subject_id";
$subject_result = $conn->query($sql);
$subject = $subject_result->fetch_assoc();

if (!$subject) {
    echo "Subject not found!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $_POST['code'];
    $name = $_POST['name'];
    $description = $_POST['description'];

    $update_sql = "UPDATE subjects SET code='$code', name='$name', description='$description' WHERE id=$subject_id";
    
    if ($conn->query($update_sql) === TRUE) {
        echo "Subject updated successfully!";
        header("Location: pages-courses.php"); // Redirect back to the subjects list
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Edit Subject - <?php echo $subject['name']; ?> - Edulk</title>

    <?php include_once("../includes/css-links-inc.php"); ?>
</head>

<body>

    <?php include_once("../includes/header.php") ?>

    <?php include_once("../includes/lectures-sidebar.php") ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Edit Subject</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="">Home</a></li>
                    <li class="breadcrumb-item"><a href="">Subjects</a></li>
                    <li class="breadcrumb-item"><a href="">Edit</a></li>
                    <li class="breadcrumb-item"><a href="">Edit Subject - <?php echo $subject['name']; ?></a></li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                                <form action="edit_subject.php?subject_id=<?php echo $subject['id']; ?>" method="post">

                                    <h3 class=" mb-4 mt-2 card-title">Edit Subject - <?php echo $subject['name']; ?></h3>

                                    <div class="mb-3">
                                        <label for="name" class="form-label">Subject Code</label>
                                        <input type="text" class="form-control w-50" id="code" name="code" value="<?php echo $subject['code']; ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="name" class="form-label">Subject Name</label>
                                        <input type="text" class="form-control w-50" id="name" name="name" value="<?php echo $subject['name']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Subject Description</label>
                                        <textarea class="form-control w-50" id="description" name="description" rows="3" required><?php echo $subject['description']; ?></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success ">Update Subject</button>
                                    <a href="pages-courses.php" class="btn btn-danger">Cancel</a>
                                </form>
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
</body>

</html>

<?php

$conn->close();
?>

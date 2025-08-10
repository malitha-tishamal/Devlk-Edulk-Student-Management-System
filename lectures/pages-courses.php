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

// Fetch subjects
$sql = "SELECT * FROM subjects";
$result = $conn->query($sql);

// Fetch distinct semesters for filter
$semesters = $conn->query("SELECT DISTINCT semester FROM subjects ORDER BY semester ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Courses</title>

    <?php include_once("../includes/css-links-inc.php"); ?>
</head>

<body>

    <?php include_once("../includes/header.php") ?>
    <?php include_once("../includes/lectures-sidebar.php") ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Courses</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="">Home</a></li>
                    <li class="breadcrumb-item"><a href="">Courses</a></li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="d-flex justify-content-between mb-3">
                <!-- Semester Filter -->
                <select id="semesterFilter" class="form-select w-25">
                    <label>Select Semester : </label>
                    <option value="">All Semesters</option>
                    <?php while ($row = $semesters->fetch_assoc()) { ?>
                        <option value="<?= $row['semester'] ?>"><?= $row['semester'] ?></option>
                    <?php } ?>
                </select>
                        <input type="text" id="searchInput" class="form-control w-50" placeholder="Search courses...">
            </div>

            <table id="courseDataTable" class="table datatable">
                <thead class="align-middle text-center">
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Semester</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="courseTable">
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . $row['code'] . "</td>";
                            echo "<td class='semester'>" . $row['semester'] . "</td>";
                            echo "<td class='course-name'>" . $row['name'] . "</td>";
                            echo "<td>" . $row['description'] . "</td>";

                            // Edit Button
                            echo '<td class="text-center">
                                    <a href="edit_subject.php?subject_id=' . $row['id'] . '" class="btn btn-primary">Edit</a>
                                  </td>';

                            // Delete Button
                            echo "<td class=''>
                                    <button class='btn btn-danger btn-sm delete-btn' data-id='" . $row['id'] . "'>Delete</button>
                                  </td>";

                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>No Courses.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </main>

    <?php include_once("../includes/footer.php") ?>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
    <?php include_once("../includes/js-links-inc.php") ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            // Delete functionality
            $(".delete-btn").click(function () {
                let subjectId = $(this).data("id");

                if (confirm("Are you sure you want to delete this course?")) {
                    $.ajax({
                        url: "delete_subject.php",
                        type: "POST",
                        data: { id: subjectId },
                        success: function (response) {
                            if (response === "success") {
                                alert("Course deleted successfully!");
                                location.reload(); // Refresh page
                            } else {
                                alert("Error deleting course.");
                            }
                        }
                    });
                }
            });

            $("#searchInput").on("keyup", function () {
                let searchText = $(this).val().toLowerCase();
                $("#courseTable tr").each(function () {
                    let courseName = $(this).find(".course-name").text().toLowerCase();
                    $(this).toggle(courseName.includes(searchText));
                });
            });

            $("#semesterFilter").change(function () {
                let selectedSemester = $(this).val();
                $("#courseTable tr").each(function () {
                    let courseSemester = $(this).find(".semester").text();
                    $(this).toggle(selectedSemester === "" || courseSemester === selectedSemester);
                });
            });
        });
    </script>

</body>
</html>

<?php
$conn->close();
?>

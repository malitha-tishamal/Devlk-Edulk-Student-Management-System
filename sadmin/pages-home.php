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
$sql = "SELECT name, email, nic,mobile,profile_picture FROM sadmins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Home - Eduwlk</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <?php include_once ("../includes/css-links-inc.php"); ?>

    <style>
        /* Styling for the popup */
        .popup-message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px;
            background-color: #28a745;
            color: white;
            font-weight: bold;
            border-radius: 5px;
            display: none; /* Hidden by default */
            z-index: 9999;
        }

        .error-popup {
            background-color: #dc3545;
        }
    </style>

    <?php if (isset($_SESSION['status'])): ?>
        <div class="popup-message <?php echo ($_SESSION['status'] == 'success') ? '' : 'error-popup'; ?>" id="popup-alert">
            <?php echo $_SESSION['message']; ?>
        </div>

        <script>
            // Display the popup message
            document.getElementById('popup-alert').style.display = 'block';

            // Automatically hide the popup after 10 seconds
            setTimeout(function() {
                const popupAlert = document.getElementById('popup-alert');
                if (popupAlert) {
                    popupAlert.style.display = 'none';
                }
            }, 1000);

            // If success message, redirect to index.php after 10 seconds
            <?php if ($_SESSION['status'] == 'success'): ?>
                setTimeout(function() {
                    window.location.href = 'pages-add-admin.php'; // Redirect after 10 seconds
                }, 1000); // Delay 10 seconds before redirecting
            <?php endif; ?>
        </script>

        <?php
        // Clear session variables after showing the message
        unset($_SESSION['status']);
        unset($_SESSION['message']);
        ?>
    <?php endif; ?>

</head>

<body>

    <?php include_once ("../includes/header.php") ?>

    <?php include_once ("../includes/sadmin-sidebar.php") ?>

    <div class="toast-container top-50 start-50 translate-middle p-3">
      <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          
          <strong class="me-auto">Alert</strong>
          <!-- <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button> -->
        </div>
        <div class="toast-body" id="alert_msg">
          <!--Message Here-->
        </div>
      </div>
    </div>
    <div id="toastBackdrop" class="toast-backdrop"></div>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Home</h1>
            <nav>
                <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item">Admin</li>
                <li class="breadcrumb-item active">Home</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <?php
                // Fetch last 20 logged in students ordered by last_login DESC
                $sql_students = "SELECT name, email, profile_picture, last_login FROM students WHERE last_login IS NOT NULL ORDER BY last_login DESC LIMIT 20";
                $result_students = $conn->query($sql_students);
                ?>

                <section class="section">
                    <div class="row">
                        <h5 class="mb-4">Last 20 Logged In Students</h5>
                        <?php if ($result_students && $result_students->num_rows > 0): ?>
                            <?php while ($student = $result_students->fetch_assoc()): ?>
                                <div class="col-md-3 mb-4">
                              <div class="col-md-6 mb-4">
                              <div class="card h-100 shadow-sm border rounded-3 p-3" style="width: 450px; margin: auto;">
                                <div class="d-flex align-items-center">
                                  <!-- Profile Picture Left -->
                                  <div class="flex-shrink-0 me-4">
                                    <?php if (!empty($student['profile_picture']) && file_exists('../' . $student['profile_picture'])): ?>
                                      <img src="<?php echo '../' . htmlspecialchars($student['profile_picture']); ?>" alt="<?php echo htmlspecialchars($student['name']); ?>" class="rounded-circle border border-2" style="width:120px; height:120px; object-fit: cover; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
                                    <?php else: ?>
                                      <img src="../assets/img/default.jpg" alt="Default Profile" class="rounded-circle border border-2" style="width:120px; height:120px; object-fit: cover; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
                                    <?php endif; ?>
                                  </div>
                                  <!-- Details Right -->
                                  <div class="flex-grow-1">
                                    <h4 class="fw-bold mb-2" style="color: #212529; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                      <?php echo htmlspecialchars($student['name']); ?>
                                    </h4>
                                    <p class="text-secondary mb-2" style="font-size: 1rem; overflow-wrap: break-word;">
                                      <i class="bi bi-envelope-fill me-2" style="color:#6c757d;"></i>
                                      <?php echo htmlspecialchars($student['email']); ?>
                                    </p>
                                    <p class="text-muted mb-3" style="font-size: 0.9rem;">
                                      <i class="bi bi-clock-fill me-2"></i>Last login:<br>
                                      <small><?php echo htmlspecialchars($student['last_login']); ?></small>
                                    </p>
                                    <a href="mailto:<?php echo htmlspecialchars($student['email']); ?>" class="btn btn-outline-primary rounded-pill px-4">
                                      Contact
                                    </a>
                                  </div>
                                </div>
                              </div>
                            </div>


                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No student login records found.</p>
                        <?php endif; ?>
                    </div>
                </section>


    </main>

    <?php include_once ("../includes/footer.php") ?>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <?php include_once ("../includes/js-links-inc.php") ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            // On form submit
            $("#signup-form").submit(function(event) {
                event.preventDefault(); // Prevent form submission

                $.ajax({
                    url: "admin-register-process2.php", // Send form data to register.php
                    type: "POST",
                    data: $(this).serialize(), // Serialize the form data
                    dataType: "json", // Expect JSON response
                    success: function(response) {
                        let popupAlert = $("#popup-alert");

                        // Set the message class and text based on the response status
                        if (response.status === "success") {
                            popupAlert.removeClass("alert-error").addClass("alert-success").html(response.message);
                        } else {
                            popupAlert.removeClass("alert-success").addClass("alert-error").html(response.message);
                        }

                        // Show the alert
                        popupAlert.show();

                        // Hide the alert after 10 seconds
                        setTimeout(function() {
                            popupAlert.fadeOut();
                        }, 10000);

                        // If success, redirect after message disappears
                        if (response.status === "success") {
                            setTimeout(function() {
                                window.location.href = "add-admin.php"; // Change this to your target redirect URL
                            }, 10000); // Same 10 seconds delay before redirect
                        }
                    },
                    error: function(xhr, status, error) {
                        alert("AJAX Error: " + xhr.responseText); // Handle AJAX error
                    }
                });
            });
        });
    </script>

</body>

</html>
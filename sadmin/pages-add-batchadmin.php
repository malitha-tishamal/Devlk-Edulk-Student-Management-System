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

    <title>Add Batch Admin - Eduwlk</title>
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
                    window.location.href = 'pages-add-batchadmin.php'; // Redirect after 10 seconds
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
            <h1>Add Batch Admin</h1>
            <nav>
                <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item">Admin</li>
                <li class="breadcrumb-item active">Add Admin</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body pt-4">
                            <!-- <h5 class="card-title"></h5> -->

                            <form action="admin-register-process3.php" method="POST" class="needs-validation" novalidate>

                                <div class="row mb-3">
                                    <label for="name" class="col-lg-3 col-md-4 col-sm-4 col-form-label">Name</label>
                                    <div class="col-lg-9 col-md-8 col-sm-8">
                                        <input type="text" class="form-control" id="name" name="name" required>
                                        <div class="invalid-feedback" style="font-size:14px" id="">
                                            Please enter the name
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="email" class="col-lg-3 col-md-4 col-sm-4 col-form-label">Reg No</label>
                                    <div class="col-lg-9 col-md-8 col-sm-8">
                                        <input type="text" class="form-control" id="regno" name="regno" required>
                                        <div class="invalid-feedback" style="font-size:14px" id="">
                                            Please enter the Register Number
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="nicNumber" class="col-lg-3 col-md-4 col-sm-4 col-form-label">NIC Number</label>
                                    <div class="col-lg-9 col-md-8 col-sm-8">
                                        <input type="text" class="form-control" id="nicNumber" name="nic" placeholder="" oninput="this.value = this.value.toUpperCase(); validateNic(this);" required>
                                        <div class="invalid-feedback" style="font-size:14px;" id="nicErrorMessage">
                                            Please enter the NIC number
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="email" class="col-lg-3 col-md-4 col-sm-4 col-form-label">Email</label>
                                    <div class="col-lg-9 col-md-8 col-sm-8">
                                        <input type="email" class="form-control" id="email" name="email" required>
                                        <div class="invalid-feedback" style="font-size:14px" id="">
                                            Please enter the email address
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="mobileNumber" class="col-lg-3 col-md-4 col-sm-4 col-form-label">Mobile Number</label>
                                    <div class="col-lg-9 col-md-8 col-sm-8">
                                        <div class="input-group">
                                            <span class="input-group-text">+94</span>
                                            <input type="tel" class="form-control" id="mobileNumber" name="mobile" placeholder="712345678" oninput="validateMobile(this)" required>
                                            <div class="invalid-feedback" style="font-size:14px;" id="numberErrorMessage">
                                                Please enter the mobile number
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="email" class="col-lg-3 col-md-4 col-sm-4 col-form-label">Gender</label>
                                    <div class="col-lg-9 col-md-8 col-sm-8">
                                            <input type="radio" name="gender" value="Male" id="maleRadio" required> <label for="maleRadio">Male</label>
                                            &nbsp;&nbsp;&nbsp;&nbsp;
                                            <input type="radio" name="gender" value="Female" id="femaleRadio" required> <label for="bordRadio">Female</label>
                                        <div class="invalid-feedback" style="font-size:14px" id="">
                                            Please enter Gender
                                        </div>
                                    </div>
                                </div>
                                 
                                <div class="row mb-4">
                                    <label for="password" class="col-lg-3 col-md-4 col-sm-4 col-form-label">Password</label>
                                    <div class="col-lg-9 col-md-8 col-sm-8">
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" name="password" required>
                                            <span class="input-group-text" id="inputGroupPrepend">
                                                <i class="password-toggle-icon1 bx bxs-show" onclick="togglePasswordVisibility('password', 'password-toggle-icon1')"></i>
                                            </span>
                                            <div class="invalid-feedback" style="font-size:14px;" id="">
                                                Please enter password
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-2">                        
                                    <div class="text-center">
                                        <!-- <input type="submit" class="btn btn-primary" name="create_account" value="Create Account"> -->
                                        <input type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmSubmitModal" name="" value="Create Account">
                                    </div>
                                </div>

                                <div class="modal fade" id="confirmSubmitModal" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirmation</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Are you want to create the account?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                                <input type="submit" class="btn btn-primary" id="submitButton" name="create_account" value="Yes">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
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
                                window.location.href = "add-batchadmin.php"; // Change this to your target redirect URL
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
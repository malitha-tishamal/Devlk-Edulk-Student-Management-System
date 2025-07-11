<?php
// Start session to access session variables
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Create Lecture Account - Edulk</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <?php include_once ("includes/css-links-inc.php"); ?>
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
</head>

<body>
    <!-- Displaying the message from the session -->
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
            }, 10000);

        </script>

        <?php
        // Clear session variables after showing the message
        unset($_SESSION['status']);
        unset($_SESSION['message']);
        ?>
    <?php endif; ?>

    <main>
        <div class="container">
            <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
                            <div class="d-flex justify-content-center py-4">
                                <a href="index.php" class="logo d-flex align-items-center w-auto">
                                    <img src="assets/images/logos/edulk-logo.png" alt="" style="max-height:130px;">
                                    <!-- <span class="d-none d-lg-block">MediQ</span> -->
                                </a>
                            </div><!-- End Logo -->

                            <div class="card mb-2">
                                <div class="card-body">
                                    <div class="pt-4 pb-2">
                                        <h5 class="card-title text-center pb-0 fs-4">Create an Account</h5>
                                        <!-- <p class="text-center small">Enter your username & password to login</p> -->
                                    </div>

                                    <form action="lecure-register.php" method="POST" class="row g-3 needs-validation" novalidate>

                                        <div class="col-12">
                                         <label for="name" class="form-label">Name</label>
                                            <input type="text" class="form-control" id="name" name="username" required>
                                            <div class="invalid-feedback" style="font-size:14px" id="">
                                                Please enter the name
                                            </div>
                                        </div>

                                        <div class="col-12">
                                         <label for="nicNumber" class="form-label">NIC Number</label>
                                              <input type="text" class="form-control" id="nicNumber" name="nic" placeholder="" oninput="this.value = this.value.toUpperCase(); validateNic(this);" required>
                                            <div class="invalid-feedback" style="font-size:14px;" id="nicErrorMessage">
                                                Please enter the NIC number
                                            </div>
                                        </div>

                                        <div class="col-12">
                                          <label for="email" class="form-label">Email</label>
                                          <input type="email" class="form-control" id="email" name="email" required>
                                            <div class="invalid-feedback" style="font-size:14px" id="">
                                                Please enter the email address
                                            </div>
                                        </div>

                                        <div class="col-12">
                                          <label for="mobileNumber" class="form-label">Mobile Number</label>
                                              <div class="input-group">
                                                <span class="input-group-text">+94</span>
                                                <input type="tel" class="form-control" id="mobileNumber" name="mobile" placeholder="712345678" oninput="validateMobile(this)" required>
                                                <div class="invalid-feedback" style="font-size:14px;" id="numberErrorMessage">
                                                    Please enter the mobile number
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                          <label for="password" class="form-label">Password</label>
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

                                        <!--div class="col-12">
                                          <div class="form-check">
                                            <input class="form-check-input" name="terms" type="checkbox" value="" id="acceptTerms" required>
                                            <label class="form-check-label" for="acceptTerms">I agree and accept the <a href="#">terms and conditions</a></label>
                                            <div class="invalid-feedback">You must agree before submitting.</div>
                                          </div>
                                        </div-->
                                        <div class="col-12">
                                          <button class="btn btn-primary w-100" type="submit"
                                           data-bs-toggle="modal" data-bs-target="#confirmSubmitModal">Create Account</button>
                                        </div>

                                        <!--div class="col-12">
                                          <p class="small mb-0">Create Administator account? <a href="super-admin/admin-signup.php">Click</a></p>
                                        </div-->

                                        <div class="col-12">
                                          <p class="small mb-0">Already have an account? <a href="index.php">Log in</a></p>
                                        </div>

                                        <div class="col-12">
                                          <p class="small mb-0">Are You Student an account? <a href="pages-signup.php">Log in</a></p>
                                        </div>
                                      </form>
                                </div>
                            </div>

                            <?php include_once ("includes/footer3.php") ?>

                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main><!-- End #main -->

 
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // On form submit
            $("#signup-form").submit(function(event) {
                event.preventDefault(); // Prevent form submission

                $.ajax({
                    url: "register.php", // Send form data to register.php
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
                                window.location.href = "index.php"; // Change this to your target redirect URL
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



    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <?php include_once ("includes/js-links-inc.php") ?>

</body>

</html>
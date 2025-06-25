<?php
    function get_base_url() {
        // Get the protocol (http or https)
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

        // Get the host name
        $host = $_SERVER['HTTP_HOST'];

        // Check if it's localhost or production
        if ($host == 'localhost') {
            // Localhost path with project folder
            return $protocol . $host . '/Devlk-Edulk-Student-Management-System/';
        } else {
            // Production base URL
            return $protocol . $host . '/Devlk-Edulk-Student-Management-System/';
        }
    }

    $base_url = get_base_url();
?>

<!-- Favicon and Apple Touch Icon -->
<link href="<?php echo $base_url; ?>assets/images/logos/favicon.png" rel="icon">
<link href="<?php echo $base_url; ?>assets/img/apple-touch-icon.png" rel="apple-touch-icon">

<!-- Google Fonts -->
<link href="https://fonts.gstatic.com" rel="preconnect">
<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

<!-- Vendor CSS Files -->
<link href="<?php echo $base_url; ?>assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="<?php echo $base_url; ?>assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<link href="<?php echo $base_url; ?>assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
<link href="<?php echo $base_url; ?>assets/vendor/quill/quill.snow.css" rel="stylesheet">
<link href="<?php echo $base_url; ?>assets/vendor/quill/quill.bubble.css" rel="stylesheet">
<link href="<?php echo $base_url; ?>assets/vendor/remixicon/remixicon.css" rel="stylesheet">
<link href="<?php echo $base_url; ?>assets/vendor/simple-datatables/style.css" rel="stylesheet">

<!-- Template Main CSS File -->
<link href="<?php echo $base_url; ?>assets/css/style.css" rel="stylesheet">

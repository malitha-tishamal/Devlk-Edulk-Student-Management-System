<!-- Vendor JS Files -->
<script src="<?php echo $base_url; ?>assets/js/jquery.min.js"></script>
<script src="<?php echo $base_url; ?>assets/vendor/apexcharts/apexcharts.min.js"></script>
<script src="<?php echo $base_url; ?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo $base_url; ?>assets/vendor/chart.js/chart.umd.js"></script>
<script src="<?php echo $base_url; ?>assets/vendor/echarts/echarts.min.js"></script>
<script src="<?php echo $base_url; ?>assets/vendor/quill/quill.min.js"></script>
<script src="<?php echo $base_url; ?>assets/vendor/simple-datatables/simple-datatables.js"></script>
<script src="<?php echo $base_url; ?>assets/vendor/tinymce/tinymce.min.js"></script>
<script src="<?php echo $base_url; ?>assets/vendor/php-email-form/validate.js"></script>

<!-- Utils JS File -->
<script src="<?php echo $base_url; ?>assets/js/utils.js"></script>

<!-- Template Main JS File -->
<script src="<?php echo $base_url; ?>assets/js/main.js"></script>

	<script>
        // Disable right-click and show message
        document.addEventListener('contextmenu', function(event) {
            event.preventDefault();
            alert("Right-click is disabled on this page!");
        });

        // Disable printing and show message
        window.onbeforeprint = function() {
            alert("Printing is disabled on this page!");
            return false;
        };

        window.onafterprint = function() {
            alert("Printing is disabled on this page!");
            return false;
        };
    </script>
    
    <!--script>
(function(){var _0xabc1=["\x63\x6F\x6E\x74\x65\x78\x74\x6D\x65\x6E\x75","\x61\x64\x64\x45\x76\x65\x6E\x74\x4C\x69\x73\x74\x65\x6E\x65\x72","\x70\x72\x65\x76\x65\x6E\x74\x44\x65\x66\x61\x75\x6C\x74","\x52\x69\x67\x68\x74\x2D\x63\x6C\x69\x63\x6B\x20\x69\x73\x20\x64\x69\x73\x61\x62\x6C\x65\x64\x20\x6F\x6E\x20\x74\x68\x69\x73\x20\x70\x61\x67\x65\x21","\x61\x6C\x65\x72\x74","\x6F\x6E\x62\x65\x66\x6F\x72\x65\x70\x72\x69\x6E\x74","\x50\x72\x69\x6E\x74\x69\x6E\x67\x20\x69\x73\x20\x64\x69\x73\x61\x62\x6C\x65\x64\x20\x6F\x6E\x20\x74\x68\x69\x73\x20\x70\x61\x67\x65\x21","\x6F\x6E\x61\x66\x74\x65\x72\x70\x72\x69\x6E\x74"];document[_0xabc1[1]](_0xabc1[0],function(_0xdef1){_0xdef1[_0xabc1[2]]();window[_0xabc1[4]](_0xabc1[3])});window[_0xabc1[5]]=function(){window[_0xabc1[4]](_0xabc1[6]);return false};window[_0xabc1[7]]=function(){window[_0xabc1[4]](_0xabc1[6]);return false}})();
</script-->

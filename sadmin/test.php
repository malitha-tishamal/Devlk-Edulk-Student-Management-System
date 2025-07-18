<?php
session_start();
require_once '../includes/db-conn.php';

// Check admin login
if (!isset($_SESSION['sadmin_id'])) {
    header("Location: ../index.php");
    exit();
}

$uploadDir = '../uploads/';
$maxUploadMB = 100; // max 100MB total usage

// Function to get total used space in uploads directory (MB)
function getUsedSpaceMB($dir) {
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) {
        if ($file->isFile()) $size += $file->getSize();
    }
    return $size / (1024 * 1024);
}

// Function for file icon (thumbnail) based on extension
function getFileIconHTML($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    switch ($ext) {
        case 'pdf': return '<i class="fa-solid fa-file-pdf" style="color:#d9534f; font-size: 32px;"></i>';
        case 'doc': case 'docx': return '<i class="fa-solid fa-file-word" style="color:#2a64bc; font-size: 32px;"></i>';
        case 'xls': case 'xlsx': return '<i class="fa-solid fa-file-excel" style="color:#218838; font-size: 32px;"></i>';
        case 'ppt': case 'pptx': return '<i class="fa-solid fa-file-powerpoint" style="color:#f0ad4e; font-size: 32px;"></i>';
        case 'jpg': case 'jpeg': case 'png': case 'gif': 
            return '<img src="../uploads/' . rawurlencode($filename) . '" alt="img" style="width:32px; height:32px; object-fit:contain; border-radius:4px;">';
        default: return '<i class="fa-solid fa-file" style="color:#6c757d; font-size: 32px;"></i>';
    }
}

// Handle file upload via AJAX POST (upload-file.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    header('Content-Type: application/json');

    $file = $_FILES['file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Upload error']);
        exit;
    }

    // Check file size limit per file (100MB max)
    if ($file['size'] > 100 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File too large. Max 100MB per file.']);
        exit;
    }

    // Check total used + new file size <= maxUploadMB
    $used = getUsedSpaceMB($uploadDir);
    if (($used + ($file['size'] / (1024 * 1024))) > $maxUploadMB) {
        echo json_encode(['success' => false, 'message' => 'Upload exceeds total storage limit of 100MB']);
        exit;
    }

    // Generate unique file name to avoid conflicts
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safeName = uniqid('file_', true) . '.' . $ext;

    $dest = $uploadDir . $safeName;

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        // Optionally, insert file record into DB here

        echo json_encode(['success' => true, 'filename' => $safeName]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
    }
    exit;
}

// Get list of uploaded files
$uploadedFiles = array_filter(scandir($uploadDir), function($f) use ($uploadDir) {
    return is_file($uploadDir . $f) && $f !== '.' && $f !== '..';
});

$usedMB = getUsedSpaceMB($uploadDir);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>Admin File Upload - Drive Usage</title>
    <?php include_once("../includes/css-links-inc.php"); ?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        body { padding: 20px; font-family: Arial, sans-serif; }
        #progressContainer { width: 100%; background: #e9ecef; border-radius: 5px; overflow: hidden; margin-top: 10px; display:none; }
        #progressBar { height: 25px; width: 0%; background: #0d6efd; text-align: center; color: white; line-height: 25px; font-weight: bold; transition: width 0.3s; }
        .file-thumb { display: inline-block; margin: 10px; text-align: center; cursor: pointer; }
        .file-thumb span { display: block; max-width: 80px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 14px; }
    </style>
</head>
<body>
<h1>Admin File Upload</h1>

<p>Drive usage: <strong><?= number_format($usedMB, 2) ?> MB</strong> / <?= $maxUploadMB ?> MB</p>

<form id="uploadForm" enctype="multipart/form-data">
    <label for="fileInput">Select file to upload (Max 100MB per file):</label><br>
    <input type="file" id="fileInput" name="file" required>
    <button type="submit">Upload</button>
</form>

<div id="progressContainer">
    <div id="progressBar">0%</div>
</div>

<h2>Uploaded Files</h2>
<div id="filesContainer">
    <?php foreach ($uploadedFiles as $file): ?>
        <div class="file-thumb" title="<?= htmlspecialchars($file) ?>" onclick="window.open('../uploads/<?= rawurlencode($file) ?>', '_blank')">
            <?= getFileIconHTML($file) ?>
            <span><?= htmlspecialchars($file) ?></span>
        </div>
    <?php endforeach; ?>
</div>

<script>
    const form = document.getElementById('uploadForm');
    const fileInput = document.getElementById('fileInput');
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('progressBar');
    const filesContainer = document.getElementById('filesContainer');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (!fileInput.files.length) return alert('Please select a file');

        const file = fileInput.files[0];
        if (file.size > 100 * 1024 * 1024) {
            alert('File too large. Max 100MB allowed.');
            return;
        }

        const formData = new FormData();
        formData.append('file', file);

        const xhr = new XMLHttpRequest();

        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percent + '%';
                progressBar.textContent = percent + '%';
                progressContainer.style.display = 'block';
            }
        };

        xhr.onload = function() {
            if (xhr.status === 200) {
                const resp = JSON.parse(xhr.responseText);
                if (resp.success) {
                    alert('Upload completed: ' + resp.filename);
                    // Add new file thumbnail dynamically
                    const div = document.createElement('div');
                    div.className = 'file-thumb';
                    div.title = resp.filename;
                    div.style.cursor = 'pointer';
                    div.onclick = () => window.open('../uploads/' + encodeURIComponent(resp.filename), '_blank');

                    // Decide icon HTML for file extension
                    function getFileIconHTML(filename) {
                        const ext = filename.split('.').pop().toLowerCase();
                        switch (ext) {
                            case 'pdf': return '<i class="fa-solid fa-file-pdf" style="color:#d9534f; font-size: 32px;"></i>';
                            case 'doc':
                            case 'docx': return '<i class="fa-solid fa-file-word" style="color:#2a64bc; font-size: 32px;"></i>';
                            case 'xls':
                            case 'xlsx': return '<i class="fa-solid fa-file-excel" style="color:#218838; font-size: 32px;"></i>';
                            case 'ppt':
                            case 'pptx': return '<i class="fa-solid fa-file-powerpoint" style="color:#f0ad4e; font-size: 32px;"></i>';
                            case 'jpg':
                            case 'jpeg':
                            case 'png':
                            case 'gif': return '<img src="../uploads/' + encodeURIComponent(filename) + '" alt="img" style="width:32px; height:32px; object-fit:contain; border-radius:4px;">';
                            default: return '<i class="fa-solid fa-file" style="color:#6c757d; font-size: 32px;"></i>';
                        }
                    }

                    div.innerHTML = getFileIconHTML(resp.filename) + '<span>' + resp.filename + '</span>';
                    filesContainer.appendChild(div);

                    // Reset progress bar
                    progressBar.style.width = '0%';
                    progressBar.textContent = '0%';
                    progressContainer.style.display = 'none';

                    form.reset();
                } else {
                    alert('Upload failed: ' + resp.message);
                }
            } else {
                alert('Upload error. Status: ' + xhr.status);
            }
        };

        xhr.onerror = function() {
            alert('Upload failed due to a network error.');
        };

        xhr.open('POST', '<?= basename(__FILE__) ?>', true);
        xhr.send(formData);
    });
</script>

</body>
</html>

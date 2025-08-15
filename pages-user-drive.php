<?php
session_start();
require_once 'includes/db-conn.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['student_id'];
$user_role = 'student';
$user_folder_name = $user_role . '_' . $user_id;
$uploadDir = 'uploads/drive/' . $user_folder_name . '/';

function escapeHtml($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function getFileIconHtml($ext, $url = '') {
    $ext = strtolower($ext);
    $imgExts = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
    if (in_array($ext, $imgExts) && $url !== '') {
        return '<img src="' . escapeHtml($url) . '" class="card-img-top file-thumbnail" alt="img">';
    }

    switch ($ext) {
        case 'pdf': return '<i class="fa-solid fa-file-pdf text-danger file-icon"></i>';
        case 'doc': case 'docx': return '<i class="fa-solid fa-file-word text-primary file-icon"></i>';
        case 'xls': case 'xlsx': return '<i class="fa-solid fa-file-excel text-success file-icon"></i>';
        case 'ppt': case 'pptx': return '<i class="fa-solid fa-file-powerpoint text-warning file-icon"></i>';
        case 'zip': case 'rar': return '<i class="fa-solid fa-file-archive file-icon" style="color:#fd7e14;"></i>';
        default: return '<i class="fa-solid fa-file text-secondary file-icon"></i>';
    }
}

// Handle file deletion via AJAX
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['filename'])) {
    $fileToDelete = basename($_POST['filename']);
    $fullPath = $uploadDir . $fileToDelete;
    if (file_exists($fullPath)) {
        unlink($fullPath);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'File not found']);
    }
    exit();
}

// Handle uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['action'])) {
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Calculate current usage
    $filesNow = array_filter(scandir($uploadDir), function($f){ return $f !== '.' && $f !== '..'; });
    $totalUsedBytes = 0;
    foreach ($filesNow as $file) {
        $totalUsedBytes += filesize($uploadDir . $file);
    }
    $maxQuotaBytes = 100 * 1024 * 1024; // 100MB max

    $uploadedFiles = [];
    foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
        $file_name = basename($_FILES['files']['name'][$key]);
        $file_size = $_FILES['files']['size'][$key];

        if (($totalUsedBytes + $file_size) > $maxQuotaBytes) {
            // Skip file if exceeds quota
            continue;
        }

        $file_path = $uploadDir . $file_name;
        if (move_uploaded_file($tmp_name, $file_path)) {
            $uploadedFiles[] = $file_name;
            $totalUsedBytes += $file_size;
        }
    }

    $responseFiles = [];
    foreach ($uploadedFiles as $fName) {
        $path = $uploadDir . $fName;
        $responseFiles[] = [
            'name' => $fName,
            'size' => filesize($path),
            'ext' => pathinfo($fName, PATHINFO_EXTENSION),
            'url' => 'uploads/drive/' . $user_folder_name . '/' . rawurlencode($fName),
        ];
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'uploaded' => $responseFiles]);
    exit();
}

// Fetch user info
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Display existing files
$files = [];
if (is_dir($uploadDir)) {
    foreach (scandir($uploadDir) as $f) {
        if ($f !== '.' && $f !== '..') {
            $filePath = $uploadDir . $f;
            $files[] = [
                'name' => $f,
                'size' => filesize($filePath),
                'ext' => pathinfo($f, PATHINFO_EXTENSION),
                'url' => 'uploads/drive/' . $user_folder_name . '/' . rawurlencode($f),
                'mtime' => filemtime($filePath),
            ];
        }
    }
}

// Calculate total used bytes & quota
$totalUsedBytes = 0;
foreach ($files as $file) {
    $totalUsedBytes += $file['size'];
}
$maxQuotaBytes = 100 * 1024 * 1024;
$usagePercent = min(100, ($totalUsedBytes / $maxQuotaBytes) * 100);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1" name="viewport" />
  <title>User Drive - Edulk</title>
  <?php include_once("includes/css-links-inc.php"); ?>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <style>
    .file-thumbnail {
      height: 150px;
      object-fit: cover;
      border-bottom: 1px solid #ddd;
      border-radius: 8px 8px 0 0;
    }
    .file-icon {
      font-size: 5rem;
      display: block;
      margin: 30px auto 20px auto;
      opacity: 0.7;
    }
    .card-file {
      width: 220px;
      margin: 15px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
      transition: box-shadow 0.3s ease;
      border-radius: 8px;
      background: #fff;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .card-file:hover {
      box-shadow: 0 6px 15px rgba(0,0,0,0.2);
    }
    .card-body {
      padding: 12px 15px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .file-name {
      font-weight: 600;
      font-size: 1rem;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      margin-bottom: 5px;
    }
    .file-info {
      font-size: 0.875rem;
      color: #555;
      margin-bottom: 12px;
      line-height: 1.3;
    }
    .btn-group {
      display: flex;
      justify-content: center;
      gap: 10px;
    }
    .btn-sm {
      font-size: 0.8rem;
      padding: 5px 10px;
      flex: 1;
    }
    @media (max-width: 600px) {
      .card-file {
        width: 100%;
        margin: 10px 0;
      }
    }
    .upload-progress-container {
      margin-top: 10px;
    }
  </style>
</head>
<body>
<?php include_once("includes/header.php") ?>
<?php include_once("includes/student-sidebar.php") ?>

<main id="main" class="main">
  <div class="pagetitle mb-4">
    <h1>User Drive (<?= escapeHtml($user['name'] ?? 'User ID: ' . $user_id) ?>)</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active">User Drive</li>
      </ol>
    </nav>
  </div>

  <!-- Storage Usage -->
  <div class="mb-4">
    <label class="form-label fw-bold">Storage Usage</label>
    <div class="progress" style="height: 25px;">
      <div
        class="progress-bar <?= ($usagePercent > 80 ? 'bg-danger' : 'bg-success') ?>"
        role="progressbar"
        style="width: <?= $usagePercent ?>%;"
        aria-valuenow="<?= $usagePercent ?>"
        aria-valuemin="0"
        aria-valuemax="100"
      >
        <?= number_format($usagePercent, 2) ?>%
      </div>
    </div>
    <small class="text-muted">
      <?= number_format($totalUsedBytes / 1024 / 1024, 2) ?> MB used of 100 MB
    </small>
  </div>

  <!-- Upload Card -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body">
      <h5 class="card-title mb-3">Upload Files</h5>
      <form id="uploadForm" enctype="multipart/form-data" class="row g-2 align-items-center">
        <div class="col-sm-8">
          <input
            type="file"
            name="files[]"
            id="fileInput"
            class="form-control w-75"
            multiple
            <?= ($totalUsedBytes >= $maxQuotaBytes) ? 'disabled' : '' ?>
            required
          />
        </div>
        <div class="col-sm-4 d-flex align-items-center">
          <button
            type="submit"
            class="btn btn-primary w-25"
            <?= ($totalUsedBytes >= $maxQuotaBytes) ? 'disabled' : '' ?>
          >
            <i class="fa fa-upload me-2"></i> Upload
          </button>
        </div>
      </form>

      <div id="uploadProgressContainer" class="upload-progress-container"></div>

      <div id="uploadStatus" class="mt-3"></div>
    </div>
  </div>

  <!-- Files Card Grid -->
  <div class="d-flex flex-wrap justify-content-start">
    <?php if (count($files) === 0): ?>
      <p class="text-muted">No files uploaded yet.</p>
    <?php else: ?>
      <?php foreach ($files as $file):
        $uploadTime = date('Y-m-d H:i:s', $file['mtime']);
      ?>
        <div class="card card-file file-row" data-filename="<?= escapeHtml($file['name']) ?>">
          <?= getFileIconHtml($file['ext'], $file['url']) ?>
          <div class="card-body">
            <div class="file-name" title="<?= escapeHtml($file['name']) ?>"><?= escapeHtml($file['name']) ?></div>
            <div class="file-info">
              Size: <?= number_format($file['size'] / 1024, 2) ?> KB<br />
              Uploaded: <?= $uploadTime ?>
            </div>
            <div class="btn-group">
              <a href="<?= $file['url'] ?>" class="btn btn-sm btn-outline-primary" download="<?= escapeHtml($file['name']) ?>" title="Download">
                <i class="fa fa-download"></i>
              </a>
              <button
                class="btn btn-sm btn-outline-danger btn-delete-file"
                title="Delete"
                data-filename="<?= escapeHtml($file['name']) ?>"
              >
                <i class="fa fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>

<script>
  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  const uploadForm = document.getElementById('uploadForm');
  const uploadStatus = document.getElementById('uploadStatus');
  const progressContainer = document.getElementById('uploadProgressContainer');

  uploadForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const files = document.getElementById('fileInput').files;
    if (!files.length) return;

    progressContainer.innerHTML = '';
    uploadStatus.innerHTML = '';
    uploadForm.querySelector('button[type="submit"]').disabled = true;
    uploadForm.querySelector('#fileInput').disabled = true;

    let completed = 0;

    Array.from(files).forEach(file => {
      const progressWrapper = document.createElement('div');
      progressWrapper.className = 'mb-2';

      const label = document.createElement('div');
      label.textContent = `Uploading: ${file.name}`;
      label.style.fontWeight = '600';

      const progress = document.createElement('progress');
      progress.max = 100;
      progress.value = 0;
      progress.style.width = '100%';
      progress.style.height = '20px';

      progressWrapper.appendChild(label);
      progressWrapper.appendChild(progress);
      progressContainer.appendChild(progressWrapper);

      const formData = new FormData();
      formData.append('files[]', file);

      const xhr = new XMLHttpRequest();
      xhr.open('POST', '', true);

      xhr.upload.addEventListener('progress', e => {
        if (e.lengthComputable) {
          const percent = Math.round((e.loaded / e.total) * 100);
          progress.value = percent;
        }
      });

      xhr.onload = () => {
        if (xhr.status === 200) {
          try {
            const res = JSON.parse(xhr.responseText);
            if (res.success && res.uploaded.length) {
              location.reload();
            } else {
              uploadStatus.innerHTML = '<div class="alert alert-danger">Upload failed.</div>';
            }
          } catch {
            uploadStatus.innerHTML = '<div class="alert alert-danger">Invalid server response.</div>';
          }
        } else {
          uploadStatus.innerHTML = `<div class="alert alert-danger">Upload failed. Status: ${xhr.status}</div>`;
        }
        completed++;
        if (completed === files.length) {
          uploadForm.querySelector('button[type="submit"]').disabled = false;
          uploadForm.querySelector('#fileInput').disabled = false;
          document.getElementById('fileInput').value = '';
          progressContainer.innerHTML = '';
        }
      };

      xhr.onerror = () => {
        uploadStatus.innerHTML = '<div class="alert alert-danger">Upload error occurred.</div>';
        completed++;
        if (completed === files.length) {
          uploadForm.querySelector('button[type="submit"]').disabled = false;
          uploadForm.querySelector('#fileInput').disabled = false;
          document.getElementById('fileInput').value = '';
          progressContainer.innerHTML = '';
        }
      };

      xhr.send(formData);
    });
  });

  document.body.addEventListener('click', function (e) {
    if (e.target.closest('.btn-delete-file')) {
      const btn = e.target.closest('.btn-delete-file');
      const filename = btn.dataset.filename;

      if (!filename) return;

      if (!confirm(`Are you sure you want to delete "${filename}"?`)) return;

      btn.disabled = true;

      fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({action: 'delete', filename: filename})
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          const card = document.querySelector(`.file-row[data-filename="${CSS.escape(filename)}"]`);
          if (card) card.remove();
          location.reload();
        } else {
          alert(data.message || 'Failed to delete file.');
          btn.disabled = false;
        }
      })
      .catch(() => {
        alert('Failed to delete file due to a network error.');
        btn.disabled = false;
      });
    }
  });
</script>

<?php include_once ("includes/footer.php") ?>
<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
<?php include_once ("includes/js-links-inc.php") ?>

</body>
</html>

<?php $conn->close(); ?>

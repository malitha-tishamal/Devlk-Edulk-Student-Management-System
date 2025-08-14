<?php
session_start();
require_once '../includes/db-conn.php';

if (!isset($_SESSION['sadmin_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['sadmin_id'];
$sql = "SELECT name, email, nic, mobile, profile_picture FROM sadmins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch subject list
$subjectList = [];
$subjectQuery = $conn->query("SELECT id, name FROM subjects");
while ($row = $subjectQuery->fetch_assoc()) {
    $subjectList[] = $row;
}

// Fetch videos with uploader name & role
$videos = [];
$videoQuery = $conn->query("
    SELECT v.*,
           CASE 
               WHEN v.role = 'superadmin' THEN s.name
               WHEN v.role = 'lecture' THEN l.name
               ELSE 'Admin'
           END AS uploader_name
    FROM recordings v
    LEFT JOIN sadmins s ON v.created_by = s.id AND v.role = 'superadmin'
    LEFT JOIN lectures l ON v.created_by = l.id AND v.role = 'lecture'
    ORDER BY v.release_time DESC
");
while ($row = $videoQuery->fetch_assoc()) {
    $videos[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Upload Lecture Recording</title>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />
  <?php include_once("../includes/css-links-inc.php"); ?>
  <style>
    .progress { height: 25px; background: #f5f5f5; border-radius: 4px; overflow: hidden; margin-bottom: 15px; }
    .progress-bar { height: 100%; background-color: #007bff; width: 0; color: #fff; text-align: center; line-height: 25px; transition: width 0.3s; }
    .video-card { cursor: pointer; transition: 0.2s; position: relative; }
    .video-card:hover { box-shadow: 0 0 8px #007bff; }
    .video-card.disabled { opacity: 0.5;}
    .video-card .btn { min-width: 70px; margin-bottom: 4px; }
    .resource-form { border: 1px solid #ddd; padding: 10px; margin-top: 10px; border-radius: 4px; background: #f9f9f9; }
    .resource-message { margin-top: 8px; font-size: 0.9em; }
  </style>
</head>
<body>

<?php include_once("../includes/header.php"); ?>
<?php include_once("../includes/sadmin-sidebar.php"); ?>

<main id="main" class="main">
    <div class="pagetitle">
      <h1>Manage Video Uploads</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item active">Manage Video Uploads</li>
        </ol>
      </nav>
    </div>

  <?php
  if (isset($_SESSION['message'])) {
      echo "<div class='alert alert-success'>" . htmlspecialchars($_SESSION['message']) . "</div>";
      unset($_SESSION['message']);
  }
  ?>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h5 class="card-title">Video Upload</h5>
      <form id="uploadForm" action="upload-recording-process.php" method="POST" enctype="multipart/form-data">
        <!-- existing upload form fields here -->
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Subject</label>
            <select name="subject_id" class="form-select" required>
              <option value="">-- Select Subject --</option>
              <?php foreach ($subjectList as $sub): ?>
                <option value="<?= htmlspecialchars($sub['id']) ?>"><?= htmlspecialchars($sub['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required />
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Lecture Type</label>
            <select name="lecture_type" class="form-select">
              <option value="Zoom">Zoom Session Record</option>
              <option value="physical">Physical Lecture Record</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Access</label>
            <select name="access_level" class="form-select">
              <option value="public">Public</option>
              <option value="batch">Batch Only</option>
              <option value="private">Private</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Thumbnail</label>
            <input type="file" name="thumbnail" accept="image/*" class="form-control" onchange="previewThumbnail(event)">
            <img id="thumbPreview" src="#" style="display:none; max-height: 100px; margin-top: 10px;" />
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Video File (MP4)</label>
            <input type="file" name="video_file" accept="video/mp4" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Student View Limit</label>
            <input type="number" name="view_limit_minutes" class="form-control" placeholder="e.g. 3 (views)">
          </div>
        </div>

        <div class="progress mb-3" id="progressContainer" style="display:none;">
          <div class="progress-bar" id="progressBar">0%</div>
        </div>

        <div>
          <button type="submit" class="btn btn-primary">Upload</button>
          <button type="button" id="clearBtn" class="btn btn-secondary ms-2">Clear</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="card-title">Uploaded Recordings</h5>
      <?php
      $recordings = $conn->query("SELECT r.*, s.name AS subject_name FROM recordings r JOIN subjects s ON r.subject_id = s.id ORDER BY release_time DESC");
      $current_subject = null;
      while ($row = $recordings->fetch_assoc()):
        if ($current_subject !== $row['subject_name']):
          if ($current_subject !== null) echo "</div>";
          echo "<h4 class='mt-2 text-primary mb-3'>" . htmlspecialchars($row['subject_name']) . "</h4><div class='row'>";
          $current_subject = $row['subject_name'];
        endif;
      ?>
        <div class="col-md-3 mb-3">

          <?php
    // Determine uploader name based on created_by and role
    $uploader_name = 'Unknown';

    if ($row['role'] === 'superadmin') {
        $stmtUploader = $conn->prepare("SELECT name FROM sadmins WHERE id = ?");
    } elseif ($row['role'] === 'lecture') {
        $stmtUploader = $conn->prepare("SELECT name FROM lectures WHERE id = ?");
    } else {
        $stmtUploader = null;
    }

    if ($stmtUploader) {
        $stmtUploader->bind_param("i", $row['created_by']);
        $stmtUploader->execute();
        $resultUploader = $stmtUploader->get_result();
        if ($resultUploader->num_rows > 0) {
            $uploaderRow = $resultUploader->fetch_assoc();
            $uploader_name = $uploaderRow['name'];
        }
        $stmtUploader->close();
    }
    ?>


          <div class="card video-card <?= ($row['status'] === 'disabled') ? 'disabled' : '' ?>" data-video="<?= htmlspecialchars($row['video_path']) ?>" data-id="<?= $row['id'] ?>">
            <?php if (!empty($row['thumbnail_path'])): ?>
              <img src="../<?= htmlspecialchars($row['thumbnail_path']) ?>" class="card-img-top" style="height:160px; object-fit:cover;">
            <?php else: ?>
              <video src="stream-video.php?file=<?= urlencode(basename($row['video_path'])) ?>" style="height:160px; object-fit:cover;" muted></video>
            <?php endif; ?>
            <div class="card-body">
              <h3 class="card-title mb-1" style=" white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($row['title']) ?></h3>
              <p class="text-muted mb-0" style="font-size:12px;">
                Uploaded: <?= date("Y-m-d", strtotime($row['release_time'])) ?>
                by <?= htmlspecialchars($uploader_name) ?> (<?= htmlspecialchars($row['role']) ?>)
            </p>
              <button class="btn btn-sm btn-secondary mt-1" disabled>Play Count: <?= intval($row['play_count']) ?></button>
              <button class="btn btn-sm btn-secondary mt-1" disabled>Download Count: <?= intval($row['download_count']) ?></button>


              <?php

              $resources = $conn->prepare("SELECT * FROM recording_resources WHERE recording_id = ?");
              $resources->bind_param("i", $row['id']);
              $resources->execute();
              $resResult = $resources->get_result(); 

                      ?>


              <div class="flex-grow-1 overflow-auto" style="max-height: 280px;">
                <div class="text-primary mt-2">Resources </div>
                  <?php if ($resResult->num_rows === 0): ?>
                    <p class="text-muted">No resources added.</p>
                  <?php else: while ($res = $resResult->fetch_assoc()): ?>
                    <div id="resource-card-<?= $res['id'] ?>" class="d-flex align-items-center justify-content-between p-1 mb-1 mt-1 border rounded resource-card <?= ($res['status'] ?? '') === 'disabled' ? 'disabled' : '' ?>">
                      <div class="d-flex align-items-center gap-3 flex-grow-1">
                        <div class="resource-icon">
                          <?php if (($res['type'] ?? '') === 'file'): ?>
                            <i class="fa-solid fa-file-arrow-down"></i>
                          <?php elseif (($res['type'] ?? '') === 'link'): ?>
                            <i class="fa-solid fa-link"></i>
                          <?php else: ?>
                            <i class="fa-solid fa-question"></i>
                          <?php endif; ?>
                        </div>
                        <div>
                          <div class="fw-semibold"><?= htmlspecialchars($res['title'] ?? 'No Title') ?></div>
                          <small class="text-muted">Uploaded at: <?= htmlspecialchars($res['uploaded_at'] ?? 'Unknown') ?></small>
                        </div>
                      </div>

                      <div class="d-flex align-items-center gap-2">
                        <?php if (($res['type'] ?? '') === 'file' && !empty($res['file_path'])): ?>
                          <a href="../<?= htmlspecialchars($res['file_path']) ?>" target="_blank" download class="btn btn-outline-primary btn-sm">
                            Download
                          </a>
                        <?php elseif (($res['type'] ?? '') === 'link' && !empty($res['link_url'])): ?>
                          <a href="<?= htmlspecialchars($res['link_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm">
                          Visit
                          </a>
                        <?php else: ?>
                          <span class="text-muted fst-italic">Unavailable</span>
                        <?php endif; ?>

                        <button onclick="toggleResourceStatus(<?= $res['id'] ?>)" title="Toggle Active/Disable" class="btn btn-sm btn-warning">
                          <?= ($res['status'] ?? '') === 'disabled' ? 'Enable' : 'Disable' ?>
                        </button>

                        <button onclick="deleteResource(<?= $res['id'] ?>)" title="Delete Resource" class="btn btn-sm btn-danger">
                          Delete
                        </button>
                      </div>
                    </div>
                  <?php endwhile; endif; ?>
                </div>

                <style>
                  .resource-card.disabled {
                    opacity: 0.5;
                  }
                  .resource-icon {
                    font-size: 1.6rem;
                    color: #0d6efd;
                    width: 30px;
                    text-align: center;
                  }
                 
                </style>

              <!-- Add Resource Button -->
              <button class="btn btn-sm btn-outline-primary mt-2" onclick="event.stopPropagation(); toggleResourceForm(<?= $row['id'] ?>)">Add Resource</button>

              <!-- Resource Upload Form (hidden initially) -->
              <div id="resourceForm-<?= $row['id'] ?>" class="resource-form" style="display:none;">
                <form onsubmit="return uploadResource(event, <?= $row['id'] ?>)">
                  <div class="mb-2">
                    <input type="text" name="title" placeholder="Resource title" required class="form-control form-control-sm" />
                  </div>
                  <div class="mb-2">
                    <select name="type" onchange="toggleResourceInput(this, <?= $row['id'] ?>)" class="form-select form-select-sm" required>
                      <option value="">Select type</option>
                      <option value="file">File</option>
                      <option value="link">Link</option>
                    </select>
                  </div>
                  <div class="mb-2" id="fileInputContainer-<?= $row['id'] ?>" style="display:none;">
                    <input type="file" name="resource_file" class="form-control form-control-sm" />
                  </div>
                  <div class="mb-2" id="linkInputContainer-<?= $row['id'] ?>" style="display:none;">
                    <input type="url" name="link_url" placeholder="https://example.com" class="form-control form-control-sm" />
                  </div>

                  <div class="progress mb-2" style="height: 20px; display:none;" id="uploadProgress-<?= $row['id'] ?>">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" aria-valuemin="0" aria-valuemax="100">0%</div>
                  </div>


                  <button type="submit" class="btn btn-sm btn-success">Upload Resource</button>
                </form>
                <div id="resourceMessage-<?= $row['id'] ?>" class="resource-message"></div>
              </div>
            </div>


            <div class="d-flex flex-wrap justify-content-between mt-1 align-items-center">
              <button class="btn btn-sm btn-success me-1" onclick="event.stopPropagation(); updateStatus(<?= $row['id'] ?>, 'active', this)" <?= ($row['status'] === 'active') ? 'disabled' : '' ?>>Activate</button>
              <button class="btn btn-sm btn-warning me-1" onclick="event.stopPropagation(); updateStatus(<?= $row['id'] ?>, 'disabled', this)" <?= ($row['status'] === 'disabled') ? 'disabled' : '' ?>>Disable</button>
              <button class="btn btn-sm btn-primary me-1" onclick="event.stopPropagation(); openEditModal(<?= $row['id'] ?>)">Edit</button>
              <button class="btn btn-sm btn-info me-1" onclick="event.stopPropagation(); downloadVideo('<?= htmlspecialchars($row['video_path']) ?>', <?= $row['id'] ?>, this)">Download</button>
              <button class="btn btn-sm btn-danger" onclick="event.stopPropagation(); deleteRecording(<?= $row['id'] ?>)">Delete</button>
            </div>
          </div>
        </div>
      <?php endwhile; if ($current_subject !== null) echo "</div>"; ?>
    </div>
  </div>
</main>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="editForm">
        <div class="modal-header">
          <h5 class="modal-title">Edit Recording</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="editId" />
          <div class="mb-3">
            <label for="editTitle" class="form-label">Title</label>
            <input type="text" class="form-control" name="title" id="editTitle" required />
          </div>
          <div class="mb-3">
            <label for="editDescription" class="form-label">Description</label>
            <textarea class="form-control" name="description" id="editDescription" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label for="editSubject" class="form-label">Subject</label>
            <select name="subject_id" id="editSubject" class="form-select" required>
              <option value="">-- Select Subject --</option>
              <?php foreach ($subjectList as $sub): ?>
                <option value="<?= htmlspecialchars($sub['id']) ?>"><?= htmlspecialchars($sub['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="editLectureType" class="form-label">Lecture Type</label>
            <select name="lecture_type" id="editLectureType" class="form-select">
              <option value="Zoom">Zoom Session Record</option>
              <option value="physical">Physical Lecture Record</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="editAccessLevel" class="form-label">Access Level</label>
            <select name="access_level" id="editAccessLevel" class="form-select">
              <option value="public">Public</option>
              <option value="batch">Batch Only</option>
              <option value="private">Private</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="editViewLimit" class="form-label">Student View Limit (minutes)</label>
            <input type="number" class="form-control" name="view_limit_minutes" id="editViewLimit" min="0" />
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save changes</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include_once("../includes/footer2.php"); ?>
<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
<?php include_once("../includes/js-links-inc.php"); ?>


<script>
    function toggleResourceStatus(resourceId) {
      fetch('toggle_resource_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: resourceId })
      })
      .then(res => res.json())
      .then(data => {
        if (data.error) {
          alert('Error: ' + data.error);
          return;
        }
        const card = document.getElementById('resource-card-' + resourceId);
        if (!card) return;

        const toggleBtn = card.querySelector('button[title="Toggle Active/Disable"]');
        const links = card.querySelectorAll('.resource-actions a, a.btn');

        if (data.status === 'disabled') {
          card.classList.add('disabled');
          toggleBtn.textContent = 'Enable';
          // Disable links/buttons except toggle and delete
          card.querySelectorAll('a.btn').forEach(el => el.style.pointerEvents = 'none');
        } else {
          card.classList.remove('disabled');
          toggleBtn.textContent = 'Disable';
          card.querySelectorAll('a.btn').forEach(el => el.style.pointerEvents = '');
        }
      })
      .catch(err => {
        alert('Error toggling status: ' + err);
      });
    }

    function deleteResource(resourceId) {
      if (!confirm('Are you sure you want to delete this resource?')) return;
      fetch('delete-video-resource.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: resourceId })
      })
      .then(res => res.json())
      .then(data => {
        if (data.error) {
          alert('Error deleting resource: ' + data.error);
          return;
        }
        const card = document.getElementById('resource-card-' + resourceId);
        if (card) card.remove();
      })
      .catch(err => {
        alert('Error deleting resource: ' + err);
      });
    }
  </script>
<script>
function previewThumbnail(event) {
  const reader = new FileReader();
  reader.onload = function () {
    const preview = document.getElementById('thumbPreview');
    preview.src = reader.result;
    preview.style.display = 'block';
  };
  reader.readAsDataURL(event.target.files[0]);
}

document.getElementById('clearBtn').addEventListener('click', function() {
  document.getElementById('uploadForm').reset();
  document.getElementById('thumbPreview').style.display = 'none';
});

document.getElementById('uploadForm').addEventListener('submit', function(e) {
  const form = e.target;
  const formData = new FormData(form);
  const xhr = new XMLHttpRequest();
  const progressContainer = document.getElementById('progressContainer');
  const progressBar = document.getElementById('progressBar');

  e.preventDefault();
  xhr.open('POST', form.action, true);

  xhr.upload.addEventListener('progress', function(e) {
    if (e.lengthComputable) {
      const percent = Math.round((e.loaded / e.total) * 100);
      progressBar.style.width = percent + '%';
      progressBar.textContent = percent + '%';
      progressContainer.style.display = 'block';
    }
  });

  xhr.onload = function() {
    if (xhr.status === 200) {
      alert('Upload complete!');
      form.reset();
      progressContainer.style.display = 'none';
      progressBar.style.width = '0%';
      document.getElementById('thumbPreview').style.display = 'none';
      location.reload();
    } else {
      alert('Upload failed: ' + xhr.responseText);
    }
  };

  xhr.send(formData);
});

document.querySelectorAll('.video-card img.card-img-top').forEach(thumbnail => {
  thumbnail.addEventListener('click', function (e) {
    e.stopPropagation();
    const card = this.closest('.video-card');
    const videoPath = card.dataset.video;
    const recordingId = parseInt(card.dataset.id);
    openModal(videoPath, recordingId);
  });
});

function openModal(videoPath, recordingId) {
  const modalVideo = document.getElementById('modalVideo');
  modalVideo.src = 'stream-video.php?file=' + encodeURIComponent(videoPath.split('/').pop());
  const modal = new bootstrap.Modal(document.getElementById('videoModal'));
  modal.show();

  // Fetch related resources
  fetch('get-video-resources.php?recording_id=' + recordingId)
    .then(res => res.json())
    .then(data => {
      const container = document.getElementById('resourceContainer');
      container.innerHTML = '';
      if (data.length === 0) {
        container.innerHTML = "<p class='text-muted'>No additional resources available.</p>";
        return;
      }

      data.forEach(res => {
        const item = document.createElement('div');
        item.className = 'd-flex justify-content-between align-items-center mb-2 p-2 rounded ' + (res.status === 'disabled' ? 'bg-light text-muted opacity-50' : 'bg-white');

        item.innerHTML = `
          <div>
            <strong>${res.name}</strong><br/>
            <small>${res.type} - <a href="${res.path}" target="_blank">Open</a></small>
          </div>
          <div>
            <button class="btn btn-sm btn-warning me-2" onclick="toggleResourceStatus(${res.id}, '${res.status}')">${res.status === 'active' ? 'Disable' : 'Enable'}</button>
            <button class="btn btn-sm btn-danger" onclick="deleteResource(${res.id})">Delete</button>
          </div>
        `;
        container.appendChild(item);
      });
    });

  // Increment play count on modal open
  fetch('update-play-count.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ id: recordingId })
  })
  .then(res => res.json())
  .then(data => {
    if (!data.error) {
      // Update play count UI
      const cards = document.querySelectorAll('.video-card');
      cards.forEach(card => {
        if (parseInt(card.dataset.id) === recordingId) {
          const btns = card.querySelectorAll('button.btn-secondary');
          btns[0].textContent = `Play Count: ${data.count}`;
        }
      });
    }
  });
}

function updateStatus(id, newStatus, btn) {
  if (!confirm(`Are you sure you want to mark this video as "${newStatus}"?`)) return;

  fetch('update-recording-status.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ id, status: newStatus })
  })
  .then(res => res.json())
  .then(data => {
    if (data.error) return alert('Error: ' + data.error);
    alert(data.success);

    const card = btn.closest('.video-card');
    if (newStatus === 'disabled') {
      card.classList.add('disabled');
      card.querySelector('.btn-warning').disabled = true;
      card.querySelector('.btn-success').disabled = false;
    } else {
      card.classList.remove('disabled');
      card.querySelector('.btn-success').disabled = true;
      card.querySelector('.btn-warning').disabled = false;
    }
  })
  .catch(err => alert('Error: ' + err));
}

function downloadVideo(videoPath, recordingId, btn) {
  const link = document.createElement('a');
  link.href = 'stream-video.php?file=' + encodeURIComponent(videoPath.split('/').pop());
  link.download = '';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);

  fetch('update-download-count.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: recordingId })
  })
  .then(res => res.json())
  .then(data => {
    if (!data.error) {
      // Live update the download count button
      const card = document.querySelector(`.video-card[data-id='${recordingId}']`);
      if (card) {
        const btns = card.querySelectorAll('.btn-secondary');
        if (btns.length > 1) {
          btns[1].textContent = `Download Count: ${data.count}`;
        }
      }
    }
  });
}


function deleteRecording(id) {
  if (!confirm('Are you sure you want to delete this recording?')) return;

  fetch('delete-recording.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ id })
  })
  .then(res => res.text())
  .then(data => {
    alert(data);
    location.reload();
  })
  .catch(err => alert('Error: ' + err));
}

// Disable right click on video elements to block "Save as"
document.addEventListener('contextmenu', function(e) {
  if (e.target.tagName === 'VIDEO') {
    e.preventDefault();
  }
});

// Edit modal code
const editModal = new bootstrap.Modal(document.getElementById('editModal'));

function openEditModal(id) {
  fetch('get-recording.php?id=' + id)
    .then(res => res.json())
    .then(data => {
      if (data.error) {
        alert('Error fetching recording data: ' + data.error);
        return;
      }
      document.getElementById('editId').value = data.id;
      document.getElementById('editTitle').value = data.title;
      document.getElementById('editDescription').value = data.description || '';
      document.getElementById('editSubject').value = data.subject_id;
      document.getElementById('editLectureType').value = data.lecture_type || 'Zoom';
      document.getElementById('editAccessLevel').value = data.access_level || 'public';
      document.getElementById('editViewLimit').value = data.view_limit_minutes || '';
      editModal.show();
    })
    .catch(err => alert('Failed to load recording data: ' + err));
}

document.getElementById('editForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  fetch('update-recording.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.error) {
      alert('Failed to update: ' + data.error);
    } else {
      alert('Recording updated successfully!');
      editModal.hide();
      location.reload();
    }
  })
  .catch(err => alert('Error: ' + err));
});

// --- New Add Resource Feature ---

function toggleResourceForm(recordingId) {
  const form = document.getElementById(`resourceForm-${recordingId}`);
  if (form.style.display === 'none' || form.style.display === '') {
    form.style.display = 'block';
  } else {
    form.style.display = 'none';
  }
}

function toggleResourceInput(selectElem, recordingId) {
  const fileContainer = document.getElementById(`fileInputContainer-${recordingId}`);
  const linkContainer = document.getElementById(`linkInputContainer-${recordingId}`);

  if (selectElem.value === 'file') {
    fileContainer.style.display = 'block';
    linkContainer.style.display = 'none';
    fileContainer.querySelector('input').required = true;
    linkContainer.querySelector('input').required = false;
  } else if (selectElem.value === 'link') {
    fileContainer.style.display = 'none';
    linkContainer.style.display = 'block';
    fileContainer.querySelector('input').required = false;
    linkContainer.querySelector('input').required = true;
  } else {
    fileContainer.style.display = 'none';
    linkContainer.style.display = 'none';
    fileContainer.querySelector('input').required = false;
    linkContainer.querySelector('input').required = false;
  }
}



function uploadResource(event, recordingId) {
  event.preventDefault();

  const form = event.target;
  const messageDiv = document.getElementById(`resourceMessage-${recordingId}`);
  const progressBarContainer = document.getElementById(`uploadProgress-${recordingId}`);
  const progressBar = progressBarContainer.querySelector('.progress-bar');

  messageDiv.textContent = '';
  progressBarContainer.style.display = 'block';
  progressBar.style.width = '0%';
  progressBar.textContent = '0%';

  const formData = new FormData(form);
  formData.append('recording_id', recordingId);

  const xhr = new XMLHttpRequest();
  xhr.open('POST', 'add-video-resource.php');

  xhr.upload.onprogress = function(e) {
    if (e.lengthComputable) {
      const percent = Math.round((e.loaded / e.total) * 100);
      progressBar.style.width = percent + '%';
      progressBar.textContent = percent + '%';
    }
  };

  xhr.onload = function() {
    progressBar.style.width = '100%';
    progressBar.textContent = 'Upload complete';

    if (xhr.status === 200) {
      let data;
      try {
        data = JSON.parse(xhr.responseText);
      } catch (err) {
        messageDiv.style.color = 'red';
        messageDiv.textContent = 'Invalid server response';
        progressBarContainer.style.display = 'none';
        return;
      }

      if (data.error) {
        messageDiv.style.color = 'red';
        messageDiv.textContent = data.error;
      } else {
        messageDiv.style.color = 'green';
        messageDiv.textContent = 'Resource uploaded successfully!';
        form.reset();
        form.querySelector('select[name="type"]').value = '';
        toggleResourceInput(form.querySelector('select[name="type"]'), recordingId);

        setTimeout(() => {
          location.reload();
        }, 500);
      }
    } else {
      messageDiv.style.color = 'red';
      messageDiv.textContent = `Upload failed: ${xhr.statusText}`;
    }

    setTimeout(() => {
      progressBarContainer.style.display = 'none';
      progressBar.style.width = '0%';
      progressBar.textContent = '0%';
    }, 3000);
  };

  xhr.onerror = function() {
    messageDiv.style.color = 'red';
    messageDiv.textContent = 'Upload failed due to a network error.';
    progressBarContainer.style.display = 'none';
  };

  xhr.send(formData);

  return false;
}


</script>

<!-- Video play modal -->
<div class="modal fade" id="videoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Lecture Video</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <video id="modalVideo" controls style="width: 1080px;" preload="metadata"></video>
      </div>
    </div>
  </div>
</div>

</body>
</html>

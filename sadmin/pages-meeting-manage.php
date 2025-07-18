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
$sql = "SELECT name, email, nic, mobile, profile_picture FROM sadmins WHERE id = ?";
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Zoom Meetings - Edulk</title>
  <?php include_once("../includes/css-links-inc.php"); ?>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
  <style>
    .expired-label { color: red; font-weight: bold; }
    .disabled-btn { pointer-events: none; opacity: 0.5; }
  </style>
</head>
<body>

<?php include_once("../includes/header.php") ?>
<?php include_once("../includes/sadmin-sidebar.php") ?>

<main id="main" class="main">
  <div class="pagetitle mb-4">
    <h1>Zoom Meeting Manager</h1>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h4 class="card-title mt-3">Paste Zoom Invitation</h4>
      <textarea id="zoomInvite" class="form-control mb-3" rows="6" placeholder="Paste Zoom invitation here..."></textarea>

      <div id="extractedDetails" style="display:none;">
        <div class="mb-3">
          <label>Meeting Title</label>
          <input type="text" id="title" class="form-control" readonly>
        </div>
        <div class="row mb-3">
          <div class="col">
            <label>Date</label>
            <input type="date" id="date" class="form-control" readonly>
          </div>
          <div class="col">
            <label>Start Time</label>
            <input type="time" id="startTime" class="form-control" readonly>
          </div>
        </div>
        <div class="mb-3">
          <label>Zoom Link</label>
          <input type="text" id="zoomLink" class="form-control" readonly />
          <span id="expiredLabel" class="expired-label" style="display:none;">Meeting Expired</span>
        </div>

        <div class="d-flex flex-wrap gap-4 align-items-end">
          <div class="mb-3 flex-grow-1">
            <label>Status (Link Expiry)</label>
            <select id="status" class="form-select">
              <option value="permanent" selected>Permanent</option>
              <option value="24h">Expire after 24h</option>
              <option value="48h">Expire after 48h</option>
              <option value="72h">Expire after 72h</option>
            </select>
          </div>

          <div class="mb-3 flex-grow-1">
            <label for="subject">Select Subject</label>
            <select id="subject" name="subject" class="form-select" required>
              <option value="">-- Select Subject --</option>
              <?php
                $subject_sql = "SELECT code, name FROM subjects";
                $subject_result = $conn->query($subject_sql);
                while ($subject = $subject_result->fetch_assoc()) {
                    $value = $subject['code'] . ' - ' . $subject['name'];
                    echo "<option value='" . htmlspecialchars($value, ENT_QUOTES) . "'>$value</option>";
                }
              ?>
            </select>

          </div>
        </div>

        <button id="startMeetingBtn" class="btn btn-primary mt-3">Start Meeting</button>
        <button id="addMeetingBtn" class="btn btn-success mt-3 ms-2">Add Meeting</button>
        <button type="button" id="clearBtn" class="btn btn-danger mt-3 ms-2">Clear</button>
        <div id="saveStatus" class="mt-3"></div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Saved Meetings</h5>
      <table class="table table-bordered" id="meetingTable">
        <thead>
          <tr>
            <th class="w-25">Title</th>
            <th>Date</th>
            <th>Time</th>
            <th>Created By</th>
            <th>Role</th>
            <th>Status (Expiry)</th>
            <th>Subject</th>
            <th>Zoom Link</th>
            <th class="w-25">Actions</th>
          </tr>
        </thead>
        <tbody id="meetingTableBody">
          <!-- Meetings loaded via JS -->
        </tbody>
      </table>
    </div>
  </div>
</main>

<?php include_once("../includes/footer2.php") ?>
<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
<?php include_once("../includes/js-links-inc.php") ?>

<script>
  const zoomInviteEl = document.getElementById("zoomInvite");
  const titleEl = document.getElementById("title");
  const dateEl = document.getElementById("date");
  const startTimeEl = document.getElementById("startTime");
  const zoomLinkEl = document.getElementById("zoomLink");
  const statusEl = document.getElementById("status");
  const subjectEl = document.getElementById("subject");
  const extractedDetails = document.getElementById("extractedDetails");
  const startMeetingBtn = document.getElementById("startMeetingBtn");
  const expiredLabel = document.getElementById("expiredLabel");
  const saveStatus = document.getElementById("saveStatus");
  const addMeetingBtn = document.getElementById("addMeetingBtn");
  const clearBtn = document.getElementById("clearBtn");

  clearBtn.addEventListener("click", () => {
    zoomInviteEl.value = "";
    titleEl.value = "";
    dateEl.value = "";
    startTimeEl.value = "";
    zoomLinkEl.value = "";
    statusEl.value = "permanent";
    subjectEl.value = "";
    extractedDetails.style.display = "none";
    saveStatus.innerHTML = "";
  });

  function parseZoomInvite(text) {
    const titleMatch = text.match(/Topic:\s*(.+)/i);
    const title = titleMatch ? titleMatch[1].trim() : '';

    const timeMatch = text.match(/Time:\s*([A-Za-z]+\s+\d{1,2},\s+\d{4})\s+(\d{1,2}:\d{2})\s*(AM|PM)/i);
    let date = '', startTime = '';
    if (timeMatch) {
      const [_, dateStr, timeStr, ampm] = timeMatch;
      const dt = new Date(`${dateStr} ${timeStr} ${ampm}`);
      if (!isNaN(dt.getTime())) {
        date = dt.toISOString().split('T')[0];
        const hours = dt.getHours().toString().padStart(2, '0');
        const minutes = dt.getMinutes().toString().padStart(2, '0');
        startTime = `${hours}:${minutes}`;
      }
    }

    const linkMatch = text.match(/https:\/\/[^\s]*zoom\.us\/[^\s]+/i);
    const zoomLink = linkMatch ? linkMatch[0].trim() : '';

    return { title, date, startTime, zoomLink };
  }

  function updateUI(data) {
    if (data.title && data.date && data.startTime && data.zoomLink) {
      extractedDetails.style.display = "block";
      titleEl.value = data.title;
      dateEl.value = data.date;
      startTimeEl.value = data.startTime;
      zoomLinkEl.value = data.zoomLink;

      const meetingStart = new Date(`${data.date}T${data.startTime}`);
      const now = new Date();
      if (now > meetingStart) {
        startMeetingBtn.classList.add("disabled-btn");
        expiredLabel.style.display = "inline";
      } else {
        startMeetingBtn.classList.remove("disabled-btn");
        expiredLabel.style.display = "none";
      }
    } else {
      extractedDetails.style.display = "none";
    }
  }

  function saveMeeting(data) {
    fetch("save_meeting.php", {
      method: "POST",
      headers: {"Content-Type": "application/x-www-form-urlencoded"},
      body: new URLSearchParams(data)
    })
    .then(res => res.json())
    .then(res => {
      if (res.success) {
        saveStatus.innerHTML = '<div class="alert alert-success">Meeting added successfully</div>';
        loadMeetings();
      } else {
        saveStatus.innerHTML = `<div class="alert alert-danger">Failed to save: ${res.message}</div>`;
      }
    })
    .catch(() => {
      saveStatus.innerHTML = '<div class="alert alert-danger">Error saving meeting.</div>';
    });
  }

  addMeetingBtn.addEventListener("click", () => {
    const title = titleEl.value.trim();
    const date = dateEl.value;
    const startTime = startTimeEl.value;
    const zoomLink = zoomLinkEl.value.trim();
    const status = statusEl.value;
    const subject = subjectEl.value;

    if (!title || !date || !startTime || !zoomLink || !subject) {
      saveStatus.innerHTML = '<div class="alert alert-warning">All fields must be filled.</div>';
      return;
    }

    const meetingStart = new Date(`${date}T${startTime}`);
    const now = new Date();
    if (now > meetingStart) {
      saveStatus.innerHTML = '<div class="alert alert-danger">Meeting is already expired. Cannot add.</div>';
      return;
    }

    saveMeeting({ title, date, start_time: startTime, zoom_link: zoomLink, status, subject });
  });

  startMeetingBtn.addEventListener("click", () => {
    if (!startMeetingBtn.classList.contains("disabled-btn")) {
      const url = zoomLinkEl.value;
      if (url) window.open(url, "_blank");
    }
  });

  zoomInviteEl.addEventListener("input", () => {
    const data = parseZoomInvite(zoomInviteEl.value);
    updateUI(data);
  });

  function loadMeetings() {
    fetch("get_meetings.php")
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById("meetingTableBody");
      tbody.innerHTML = "";
      data.forEach(row => {
        // Show subject label if you want: combine code + name or just subject id
        let subjectLabel = row.subject_name ?? row.subject ?? '-'; 
        tbody.innerHTML += `
          <tr data-id="${row.id}">
            <td>${row.title}</td>
            <td>${row.date}</td>
            <td>${row.start_time}</td>
            <td>${row.created_by ?? '-'}</td>
            <td>${row.role ?? '-'}</td>
            <td>${row.status}</td>
            <td>${subjectLabel}</td>
            <td><a href="${row.zoom_link}" target="_blank"><button type="button" class="btn btn-primary btn-sm">Join</button></a></td>
            <td>
              <button class="btn btn-success btn-sm action-btn" data-action="activate">Activate</button>
              <button class="btn btn-warning btn-sm action-btn" data-action="disable">Disable</button>
              <button class="btn btn-danger btn-sm action-btn" data-action="delete">Delete</button>
            </td>
          </tr>`;
      });

      // Attach action listeners
      document.querySelectorAll(".action-btn").forEach(button => {
        button.addEventListener("click", () => {
          const action = button.getAttribute("data-action");
          const tr = button.closest("tr");
          const meetingId = tr.getAttribute("data-id");

          if (action === "delete" && !confirm("Are you sure you want to delete this meeting?")) return;

          fetch("meeting_action.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ id: meetingId, action })
          })
          .then(res => res.json())
          .then(response => {
            if (response.success) {
              loadMeetings();
            } else {
              alert("Action failed: " + response.message);
            }
          })
          .catch(() => alert("An error occurred."));
        });
      });
    });
  }

  window.addEventListener("DOMContentLoaded", loadMeetings);
</script>

</body>
</html>

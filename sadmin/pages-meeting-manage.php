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

$meeting_id = intval($_GET['meeting_id'] ?? 0);

$stmt = $conn->prepare("SELECT id, meeting_id, resource_type, resource_data, status FROM meeting_resources WHERE meeting_id = ?");
$stmt->bind_param("i", $meeting_id);
$stmt->execute();
$result = $stmt->get_result();

$resources = [];
while ($row = $result->fetch_assoc()) {
  $resources[] = $row;  // $row contains 'id' key
}

echo json_encode($resources);


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
    .resources-row td { background: #f8f9fa; }
    .resource-container { margin: 10px 0; }
    .resource-list ul { padding-left: 20px; }
    .resource-list li { margin-bottom: 6px; }
    /* Chat styles */
    .chat-container {
      min-width: 45%;
      border: 1px solid #ccc;
      padding: 15px;
      border-radius: 8px;
      background: #fff;
      display: flex;
      flex-direction: column;
      height: 300px;
      font-size: 0.9rem;
    }
    .chat-messages {
      overflow-y: auto;
      border: 1px solid #ddd;
      padding: 10px;
      background: #fafafa;
      border-radius: 4px;
      flex-grow: 1;
      margin-bottom: 10px;
    }
    .chat-messages div {
      margin-bottom: 8px;
    }
    .chat-messages strong {
      font-weight: 600;
    }
    .chat-input-group {
      display: flex;
      gap: 8px;
    }
    .chat-input-group input[type="text"] {
      flex-grow: 1;
    }
    .disabled-btn {
  pointer-events: none;
  opacity: 0.5;
}

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

  // Load meetings and render table with resource + chat row
  function loadMeetings() {
    fetch("get_meetings.php")
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById("meetingTableBody");
      tbody.innerHTML = "";
      data.forEach(row => {
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
            <td>
                ${
                  row.status === 'disabled' || row.status === 'expired'
                    ? `<button type="button" class="btn btn-primary btn-sm disabled-btn" disabled>Join</button>`
                    : `<a href="${row.zoom_link}" target="_blank"><button type="button" class="btn btn-primary btn-sm">Join</button></a>`
                }
              </td>

            <td>
              <button class="btn btn-success btn-sm action-btn" data-action="activate">Activate</button>
              <button class="btn btn-warning btn-sm action-btn" data-action="disable">Disable</button>
              <button class="btn btn-danger btn-sm action-btn" data-action="delete">Delete</button>
              <button class="btn btn-info btn-sm resources-btn" data-id="${row.id}">Resources & Chat</button>
            </td>
          </tr>
          <tr class="resources-row" data-id="${row.id}" style="display:none;">
            <td colspan="9">
              <div class="d-flex gap-4" style="align-items: flex-start;">
                
                <!-- Resources Box -->
                <div class="resource-container flex-grow-1" style="min-width: 45%; border: 1px solid #ccc; padding: 15px; border-radius: 8px; background: #f8f9fa;">
                  <h6>Resources for "${row.title}"</h6>
                  <div class="resource-list" id="resource-list-${row.id}">Loading resources...</div>
                  <div class="resource-upload mt-3">
                    <div class='d-flex'>
                      <input type="file" class='form-control w-50' id="resource-file-${row.id}" />
                      &nbsp;&nbsp;&nbsp;
                      <button class="btn btn-sm btn-primary upload-resource-btn" data-id="${row.id}">Upload File</button>
                    </div>
                    <br/>
                    <input type="text" id="resource-link-${row.id}" placeholder="Add resource link here" class="form-control mb-2" />
                    <button class="btn btn-sm btn-secondary add-link-btn" data-id="${row.id}">Add Link</button>
                  </div>
                </div>

                <!-- Chat Box -->
                <div class="chat-container flex-grow-1">
                  <h6>Chat for "${row.title}"</h6>
                  <div class="chat-messages" id="chat-messages-${row.id}">Loading chat...</div>
                  <div class="chat-input-group">
                    <input type="text" id="chat-input-${row.id}" placeholder="Type a message..." />
                    <button class="btn btn-primary btn-sm send-chat-btn" data-id="${row.id}">Send</button>
                  </div>
                </div>

              </div>
            </td>
          </tr>
        `;
      });

      // Action buttons (activate/disable/delete)
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

      // Toggle resources + chat row
      document.querySelectorAll(".resources-btn").forEach(btn => {
        btn.addEventListener("click", () => {
          const meetingId = btn.getAttribute("data-id");
          const resourcesRow = document.querySelector(`.resources-row[data-id="${meetingId}"]`);
          if (!resourcesRow) return;

          if (resourcesRow.style.display === "none") {
            resourcesRow.style.display = "table-row";
            loadResources(meetingId);
            loadChat(meetingId);
          } else {
            resourcesRow.style.display = "none";
          }
        });
      });

      // Upload resource file
      document.querySelectorAll(".upload-resource-btn").forEach(btn => {
        btn.addEventListener("click", () => {
          const meetingId = btn.getAttribute("data-id");
          const fileInput = document.getElementById(`resource-file-${meetingId}`);
          if (fileInput.files.length === 0) {
            alert("Please select a file to upload.");
            return;
          }
          const formData = new FormData();
          formData.append("file", fileInput.files[0]);
          formData.append("meeting_id", meetingId);

          fetch("upload_resource.php", { method: "POST", body: formData })
            .then(res => res.json())
            .then(resp => {
              if (resp.success) {
                loadResources(meetingId);
                fileInput.value = "";
              } else {
                alert("Upload failed: " + resp.message);
              }
            })
            .catch(() => alert("Error uploading file."));
        });
      });


      // Add resource link
      document.querySelectorAll(".add-link-btn").forEach(btn => {
        btn.addEventListener("click", () => {
          const meetingId = btn.getAttribute("data-id");
          const linkInput = document.getElementById(`resource-link-${meetingId}`);
          const link = linkInput.value.trim();

          if (!link) {
            alert("Please enter a link.");
            return;
          }

          fetch("add_resource_link.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: new URLSearchParams({
              meeting_id: meetingId,
              resource_type: "link",
              resource_data: link
            })
          })
          .then(res => res.json())
          .then(resp => {
            if (resp.success) {
              loadResources(meetingId);
              linkInput.value = "";
            } else {
              alert("Failed to add link: " + resp.message);
            }
          })
          .catch(() => alert("Error adding link."));
        });
      });

    })
    .catch(() => {
      alert("Failed to load meetings.");
    });
  }

  // Load resources for a meeting
function loadResources(meetingId) {
  const container = document.getElementById(`resource-list-${meetingId}`);
  container.innerHTML = "Loading resources...";
  fetch(`get_meeting_resources.php?meeting_id=${meetingId}`)
    .then(res => res.json())
    .then(resources => {
      if (!resources.length) {
        container.innerHTML = "<p>No resources available.</p>";
        return;
      }

      let html = "<ul>";
resources.forEach(r => {
  const disabledStyle = r.status === "disabled" ? "style='opacity: 0.5;'" : "";
  const displayName = r.type === "file" ? r.name : r.url;

  html += `<li ${disabledStyle}>
    ${r.type === "file"
      ? `<a href="../uploads/meeting_resources/${r.name}" target="_blank" download><i class="fa fa-file"></i> ${r.name}</a>`
      : `<a href="${r.url}" target="_blank"><i class="fa fa-link"></i> ${r.url}</a>`
    }
    &nbsp;
    <button class="btn btn-sm btn-danger delete-resource-btn" data-id="${r.id}" type="button">Delete</button>
  </li>`;
});
html += "</ul>";
container.innerHTML = html;


    })
    .catch(() => {
      container.innerHTML = "<p>Error loading resources.</p>";
    });
}

document.body.addEventListener('click', e => {
  if (e.target.closest('.delete-resource-btn')) {
    const btn = e.target.closest('.delete-resource-btn');
    const resourceId = btn.getAttribute('data-id');

    if (!confirm("Are you sure you want to delete this resource?")) return;

    fetch('delete_resource.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ id: resourceId })
    })
    .then(res => res.json())
    .then(resp => {
      if (resp.success) {
        alert("Resource deleted successfully.");
        // Reload the resources list for this meeting
        const resourceList = btn.closest('.resource-list');
        if (resourceList) {
          const idMatch = resourceList.id.match(/resource-list-(\d+)/);
          if (idMatch) {
            loadResources(idMatch[1]);
          }
        }
      } else {
        alert("Failed to delete resource: " + (resp.message || 'Unknown error'));
      }
    })
    .catch(() => alert("Error deleting resource."));
  }
});




  // Chat functionality (basic polling)
  function loadChat(meetingId) {
    const chatMessages = document.getElementById(`chat-messages-${meetingId}`);
    fetch(`get_meeting_chat.php?meeting_id=${meetingId}`)
      .then(res => res.json())
      .then(messages => {
        chatMessages.innerHTML = "";
        messages.forEach(msg => {
          const time = new Date(msg.created_at).toLocaleTimeString();
          chatMessages.innerHTML += `<div><strong>${msg.user_name}:</strong> ${msg.message} <small class="text-muted">[${time}]</small></div>`;
        });
        chatMessages.scrollTop = chatMessages.scrollHeight;
      })
      .catch(() => {
        chatMessages.innerHTML = "<div>Error loading chat messages.</div>";
      });
  }

  // Send chat message
  document.body.addEventListener("click", e => {
    if (e.target.classList.contains("send-chat-btn")) {
      const meetingId = e.target.getAttribute("data-id");
      const input = document.getElementById(`chat-input-${meetingId}`);
      const message = input.value.trim();
      if (!message) return alert("Please enter a message.");
      fetch("send_meeting_chat.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ meeting_id: meetingId, message })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          input.value = "";
          loadChat(meetingId);
        } else {
          alert("Failed to send message: " + (data.message || ""));
        }
      })
      .catch(() => alert("Error sending message."));
    }
  });

  // Initial load
  loadMeetings();
</script>

</body>
</html>

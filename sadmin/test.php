<?php
session_start();
require_once '../includes/db-conn.php';

/*// Redirect if not logged in (assuming user session key is 'user_id')
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch user info if needed
$user_id = $_SESSION['user_id'];
$sql = "SELECT name, email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Zoom Meetings - User View</title>
  <?php include_once("../includes/css-links-inc.php"); ?>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
  <style>
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
  </style>
</head>
<body>

<?php include_once("../includes/header.php") ?>
<?php include_once("../includes/user-sidebar.php") // Your user sidebar ?>

<main id="main" class="main">
  <div class="pagetitle mb-4">
    <h1>Zoom Meetings</h1>
  </div>

  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Available Meetings</h5>
      <table class="table table-bordered" id="meetingTable">
        <thead>
          <tr>
            <th>Title</th>
            <th>Date</th>
            <th>Time</th>
            <th>Subject</th>
            <th>Zoom Link</th>
            <th>Resources & Chat</th>
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
  // Load meetings for user view
  function loadMeetings() {
    fetch("get_meetings.php")
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById("meetingTableBody");
      tbody.innerHTML = "";

      data.forEach(row => {
        const subjectLabel = row.subject_name ?? row.subject ?? '-';

        tbody.innerHTML += `
          <tr data-id="${row.id}">
            <td>${row.title}</td>
            <td>${row.date}</td>
            <td>${row.start_time}</td>
            <td>${subjectLabel}</td>
            <td><a href="${row.zoom_link}" target="_blank"><button type="button" class="btn btn-primary btn-sm">Join</button></a></td>
            <td><button class="btn btn-info btn-sm resources-btn" data-id="${row.id}">View</button></td>
          </tr>
          <tr class="resources-row" data-id="${row.id}" style="display:none;">
            <td colspan="6">
              <div class="d-flex gap-4" style="align-items: flex-start;">
                
                <!-- Resources Box -->
                <div class="resource-container flex-grow-1" style="min-width: 45%; border: 1px solid #ccc; padding: 15px; border-radius: 8px; background: #f8f9fa;">
                  <h6>Resources for "${row.title}"</h6>
                  <div class="resource-list" id="resource-list-${row.id}">Loading resources...</div>
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

    })
    .catch(() => {
      alert("Failed to load meetings.");
    });
  }

  // Load resources for a meeting (read-only, no delete buttons)
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
          html += `<li ${disabledStyle}>
            ${r.resource_type === "file"
              ? `<a href="../uploads/meeting_resources/${r.resource_data}" target="_blank" download><i class="fa fa-file"></i> ${r.resource_data}</a>`
              : `<a href="${r.resource_data}" target="_blank"><i class="fa fa-link"></i> ${r.resource_data}</a>`
            }
          </li>`;
        });
        html += "</ul>";
        container.innerHTML = html;
      })
      .catch(() => {
        container.innerHTML = "<p>Error loading resources.</p>";
      });
  }

  // Load chat messages for a meeting
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

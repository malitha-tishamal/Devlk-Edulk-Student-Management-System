<?php
session_start();
require_once 'includes/db-conn.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['student_id'];

// Fetch student info
$stmt = $conn->prepare("SELECT * FROM students WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get meeting ID
$meeting_id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM meetings WHERE id=?");
$stmt->bind_param("i", $meeting_id);
$stmt->execute();
$meeting = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$meeting) die("Meeting not found.");

// AJAX chat & resources
if (isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');

    if ($_GET['action'] === 'get_chat') {
        $messages = [];
        $stmt = $conn->prepare("SELECT user_name, user_role, message, created_at FROM meeting_chat WHERE meeting_id=? ORDER BY created_at ASC");
        $stmt->bind_param("i", $meeting_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($msg = $result->fetch_assoc()) $messages[] = $msg;
        echo json_encode($messages);
        exit();
    }

    if ($_GET['action'] === 'get_resources') {
        $stmt = $conn->prepare("SELECT id, resource_type, resource_data, status FROM meeting_resources WHERE meeting_id=? AND status='active'");
        $stmt->bind_param("i", $meeting_id);
        $stmt->execute();
        $resources = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        echo json_encode($resources);
        exit();
    }
}

// Handle student chat send
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'send_chat') {
    header('Content-Type: application/json; charset=utf-8');
    $message = trim($_POST['message'] ?? '');
    if ($message !== '') {
        $user_name = $user['name'];
        $stmt = $conn->prepare("INSERT INTO meeting_chat (meeting_id, user_id, user_name, user_role, message, created_at) VALUES (?, ?, ?, 'student', ?, NOW())");
        $stmt->bind_param("iiss", $meeting_id, $user_id, $user_name, $message);
        echo json_encode(['success' => $stmt->execute()]);
        $stmt->close();
    } else echo json_encode(['success' => false, 'message' => 'Empty message']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Join Meeting - Edulk</title>
<?php include_once("includes/css-links-inc.php"); ?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --primary: #4361ee;
    --primary-dark: #3a56d4;
    --secondary: #6c757d;
    --success: #28a745;
    --danger: #dc3545;
    --warning: #ffc107;
    --info: #17a2b8;
    --light: #f8f9fa;
    --dark: #343a40;
    --card-shadow: 0 12px 30px rgba(0,0,0,0.08);
    --card-shadow-hover: 0 15px 35px rgba(0,0,0,0.12);
    --border-radius: 12px;
    --transition: all 0.3s ease;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: #f5f7ff;
    font-family: 'Poppins', sans-serif;
    color: #333;
    line-height: 1.6;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px;
}

/* Header styling */
.meeting-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.meeting-title {
    font-size: 2.2rem;
    font-weight: 700;
    color: var(--primary);
    display: flex;
    align-items: center;
    gap: 12px;
}

.breadcrumb {
    display: flex;
    list-style: none;
    padding: 0;
    margin-top: 10px;
    font-size: 0.95rem;
}

.breadcrumb li:not(:last-child)::after {
    content: '/';
    margin: 0 10px;
    color: var(--secondary);
}

.breadcrumb a {
    text-decoration: none;
    color: var(--secondary);
    transition: var(--transition);
}

.breadcrumb a:hover {
    color: var(--primary);
}

/* Meeting card styling */
.meeting-card {
    background: #fff;
    padding: 30px;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    margin-bottom: 30px;
    transition: var(--transition);
}

.meeting-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--card-shadow-hover);
}

.meeting-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.meeting-status {
    display: flex;
    align-items: center;
    gap: 10px;
}

.badge {
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.badge-live {
    background: var(--danger);
    color: white;
}

.badge-scheduled {
    background: var(--info);
    color: white;
}

.meeting-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.info-card {
    background: #f8faff;
    padding: 18px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: var(--transition);
}

.info-card:hover {
    background: #edf1ff;
    transform: translateY(-3px);
}

.info-card i {
    font-size: 1.5rem;
    color: var(--primary);
    background: rgba(67, 97, 238, 0.15);
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.info-content h4 {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 5px;
    color: var(--dark);
}

.info-content p {
    color: var(--secondary);
    margin: 0;
}

.join-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 14px 28px;
    background: var(--primary);
    color: white;
    text-decoration: none;
    border-radius: var(--border-radius);
    font-weight: 600;
    font-size: 1.1rem;
    transition: var(--transition);
    border: none;
    cursor: pointer;
}

.join-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(67, 97, 238, 0.3);
}

.join-btn:disabled {
    background: #b1b7d0;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* Flex container for chat and resources */
.flex-container {
    display: flex;
    gap: 30px;
    margin-top: 20px;
    flex-wrap: wrap;
}

.flex-child {
    flex: 1 1 500px;
    background: #fff;
    padding: 25px;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
}

.section-title {
    font-size: 1.4rem;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #f0f2f7;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: var(--primary);
}

/* Chat styling */
.chat-container {
    display: flex;
    flex-direction: column;
    height: 500px;
}

.chat-messages {
    flex-grow: 1;
    overflow-y: auto;
    padding: 20px;
    background: #fafbff;
    border-radius: 10px;
    border: 1px solid #e6e9f4;
    margin-bottom: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.message {
    max-width: 85%;
    padding: 15px;
    border-radius: 15px;
    position: relative;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.message-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
}

.message-sender {
    font-weight: 600;
    font-size: 0.95rem;
}

.message-role {
    font-size: 0.75rem;
    background: rgba(108, 117, 125, 0.15);
    color: var(--secondary);
    padding: 2px 8px;
    border-radius: 20px;
    margin-left: 8px;
}

.message-time {
    font-size: 0.75rem;
    color: var(--secondary);
}

.message-content {
    font-size: 0.95rem;
    line-height: 1.5;
}

.message.instructor {
    background: #e3e8ff;
    align-self: flex-start;
    border-bottom-left-radius: 5px;
}

.message.student {
    background: #d1f0ff;
    align-self: flex-end;
    border-bottom-right-radius: 5px;
}

.message.system {
    background: #f0f0f0;
    align-self: center;
    text-align: center;
    font-size: 0.85rem;
    color: var(--secondary);
}

.chat-input-container {
    display: flex;
    gap: 12px;
}

.chat-input {
    flex-grow: 1;
    padding: 14px 18px;
    border-radius: 30px;
    border: 1px solid #e6e9f4;
    font-size: 1rem;
    transition: var(--transition);
}

.chat-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
}

.send-btn {
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
}

.send-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-3px);
}

/* Resources styling */
.resource-list {
    max-height: 400px;
    overflow-y: auto;
    padding-right: 10px;
}

.resource-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    margin-bottom: 15px;
    background: #f8faff;
    border-radius: 10px;
    transition: var(--transition);
}

.resource-item:hover {
    background: #edf1ff;
    transform: translateX(5px);
}

.resource-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    background: rgba(67, 97, 238, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    font-size: 1.3rem;
}

.resource-details {
    flex-grow: 1;
}

.resource-name {
    font-weight: 600;
    margin-bottom: 5px;
    color: var(--dark);
}

.resource-type {
    font-size: 0.85rem;
    color: var(--secondary);
}

.download-btn {
    background: rgba(67, 97, 238, 0.1);
    color: var(--primary);
    border: none;
    border-radius: 8px;
    padding: 8px 15px;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}

.download-btn:hover {
    background: var(--primary);
    color: white;
}

.empty-state {
    text-align: center;
    padding: 30px;
    color: var(--secondary);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 15px;
    color: #d1d5e0;
}

.empty-state p {
    margin-top: 10px;
}

/* Responsive design */
@media (max-width: 992px) {
    .flex-container {
        flex-direction: column;
    }
    
    .flex-child {
        flex-basis: 100%;
    }
}

@media (max-width: 768px) {
    .container {
        padding: 20px 15px;
    }
    
    .meeting-title {
        font-size: 1.8rem;
    }
    
    .meeting-info-grid {
        grid-template-columns: 1fr;
    }
    
    .chat-container {
        height: 400px;
    }
}

@media (max-width: 576px) {
    .meeting-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .meeting-card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .chat-input-container {
        flex-direction: column;
    }
    
    .send-btn {
        width: 100%;
        border-radius: 30px;
    }
}
</style>
</head>
<body>
    <?php include_once("includes/header.php") ?>
<?php include_once("includes/student-sidebar.php") ?>

<main id="main" class="main">
    <div class="pagetitle">
      <h1>Zoom Meetings</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item active">Meetings</li>
        </ol>
      </nav>
    </div>

<div class="card shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="meeting-header">
            <div>
                <h1 class="meeting-title">
                    <i class="fa-solid fa-video"></i>
                    <?= htmlspecialchars($meeting['title']) ?>
                </h1>
                <ul class="breadcrumb">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="zoom_meetings.php">Zoom Meetings</a></li>
                    <li><?= htmlspecialchars($meeting['title']) ?></li>
                </ul>
            </div>
            <div class="meeting-status">
                <?php if ($meeting['status'] === 'active' && $meeting['is_live'] == 1): ?>
                    <span class="badge badge-live"><i class="fa-solid fa-circle"></i> LIVE</span>
                <?php else: ?>
                    <span class="badge badge-scheduled"><i class="fa-regular fa-clock"></i> SCHEDULED</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="meeting-card">
            <div class="meeting-info-grid">
                <div class="info-card">
                    <i class="fa-solid fa-book"></i>
                    <div class="info-content">
                        <h4>Subject</h4>
                        <p><?= htmlspecialchars($meeting['subject'] ?? 'General') ?></p>
                    </div>
                </div>
                <div class="info-card">
                    <i class="fa-solid fa-calendar-days"></i>
                    <div class="info-content">
                        <h4>Date</h4>
                        <p><?= htmlspecialchars($meeting['date']) ?></p>
                    </div>
                </div>
                <div class="info-card">
                    <i class="fa-solid fa-clock"></i>
                    <div class="info-content">
                        <h4>Time</h4>
                        <p><?= htmlspecialchars($meeting['start_time']) ?> - <?= htmlspecialchars($meeting['end_time'] ?? '') ?></p>
                    </div>
                </div>
                <div class="info-card">
                    <i class="fa-solid fa-chalkboard-user"></i>
                    <div class="info-content">
                        <h4>Hosted By</h4>
                        <p><?= htmlspecialchars($meeting['created_by']) ?> (<?= htmlspecialchars($meeting['role']) ?>)</p>
                    </div>
                </div>
            </div>

            <a href="<?= ($meeting['status'] === 'active' && $meeting['is_live'] == 1) ? htmlspecialchars($meeting['zoom_link']) : '#' ?>"
               class="join-btn <?= ($meeting['status'] !== 'active' || $meeting['is_live'] != 1) ? 'disabled' : '' ?>"
               target="<?= ($meeting['status'] === 'active' && $meeting['is_live'] == 1) ? '_blank' : '_self' ?>"
               <?= ($meeting['status'] !== 'active' || $meeting['is_live'] != 1) ? 'disabled' : '' ?>>
                <i class="fa-solid fa-arrow-right-to-bracket"></i>
                <?= ($meeting['status'] === 'active' && $meeting['is_live'] == 1) ? 'Join Meeting Now' : 'Meeting Not Live' ?>
            </a>
        </div>

        <div class="flex-container">
            <div class="flex-child">
                <h3 class="section-title"><i class="fa-solid fa-file-lines"></i> Meeting Resources</h3>
                <div class="resource-list" id="resource-list">
                    <div class="empty-state">
                        <i class="fa-solid fa-folder-open"></i>
                        <h4>Loading Resources</h4>
                        <p>Please wait while we load the meeting resources</p>
                    </div>
                </div>
            </div>

            <div class="flex-child">
                <h3 class="section-title"><i class="fa-solid fa-comments"></i> Meeting Chat</h3>
                <div class="chat-container">
                    <div class="chat-messages" id="chat-messages">
                        <div class="message system">Loading chat messages...</div>
                    </div>
                    <div class="chat-input-container">
                        <input type="text" id="chat-input" class="chat-input" placeholder="Type your message here..." />
                        <button class="send-btn" id="send-chat-btn"><i class="fa-solid fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function loadResources() {
    const container = document.getElementById("resource-list");
    try {
        const res = await fetch(`meeting_detail.php?id=<?= $meeting_id ?>&action=get_resources`);
        const resources = await res.json();

        if (!resources.length) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fa-solid fa-folder-open"></i>
                    <h4>No Resources Available</h4>
                    <p>This meeting doesn't have any resources yet</p>
                </div>
            `;
            return;
        }

        let html = '';
        resources.forEach(r => {
            const icon = r.resource_type === 'file' ? 'fa-file' : 'fa-link';
            const type = r.resource_type === 'file' ? 'Document' : 'Web Link';

            html += `
            <div class="resource-item">
                <div class="resource-icon"><i class="fa-solid ${icon}"></i></div>
                <div class="resource-details">
                    <div class="resource-name">${r.resource_data}</div>
                    <div class="resource-type">${type}</div>
                </div>
                <a href="${r.resource_type==='file' ? 'uploads/meeting_resources/'+r.resource_data : r.resource_data}" target="_blank" class="download-btn">
                    <i class="fa-solid fa-download"></i> Download
                </a>
            </div>
            `;
        });
        container.innerHTML = html;
    } catch (e) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fa-solid fa-exclamation-triangle"></i>
                <h4>Failed to Load Resources</h4>
                <p>Please try again later</p>
            </div>
        `;
    }
}

async function refreshJoinButton() {
    try {
        const res = await fetch(`meeting_detail.php?id=<?= $meeting_id ?>&action=get_meeting_status`);
        const meeting = await res.json();
        const joinBtn = document.querySelector(".join-btn");

        if (meeting.status === "active" && meeting.is_live == 1) {
            joinBtn.classList.remove("disabled");
            joinBtn.href = meeting.zoom_link;
            joinBtn.target = "_blank";
            joinBtn.removeAttribute("disabled");
            joinBtn.innerHTML = `<i class="fa-solid fa-arrow-right-to-bracket"></i> Join Meeting Now`;
        } else {
            joinBtn.classList.add("disabled");
            joinBtn.href = "#";
            joinBtn.target = "_self";
            joinBtn.setAttribute("disabled", true);
            joinBtn.innerHTML = `<i class="fa-solid fa-arrow-right-to-bracket"></i> Meeting Not Live`;
        }
    } catch (e) {
        console.error("Failed to refresh join button", e);
    }
}

async function loadChat() {
    const chatEl = document.getElementById("chat-messages");
    try {
        const res = await fetch(`meeting_detail.php?id=<?= $meeting_id ?>&action=get_chat`);
        const msgs = await res.json();

        if (!msgs.length) {
            chatEl.innerHTML = `<div class="message system">No messages yet. Be the first to start the conversation!</div>`;
            return;
        }

        let html = '';
        msgs.forEach(m => {
            const time = new Date(m.created_at).toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
            const roleClass = m.user_role === 'student' ? 'student' : 'instructor';
            html += `
            <div class="message ${roleClass}">
                <div class="message-header">
                    <div>
                        <span class="message-sender">${m.user_name}</span>
                        <span class="message-role">${m.user_role}</span>
                    </div>
                    <div class="message-time">${time}</div>
                </div>
                <div class="message-content">${m.message}</div>
            </div>
            `;
        });
        chatEl.innerHTML = html;
        chatEl.scrollTop = chatEl.scrollHeight;
    } catch (e) {
        chatEl.innerHTML = `<div class="message system">Failed to load chat messages. Please try again.</div>`;
    }
}

document.getElementById("send-chat-btn").addEventListener("click", async () => {
    const input = document.getElementById("chat-input");
    const message = input.value.trim();
    if (!message) return input.focus();

    try {
        const formData = new URLSearchParams();
        formData.append("action", "send_chat");
        formData.append("message", message);

        const res = await fetch("meeting_detail.php?id=<?= $meeting_id ?>", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: formData
        });

        const data = await res.json();
        if (data.success) {
            input.value = "";
            loadChat();
        } else {
            alert("Failed to send message. Please try again.");
        }
    } catch (e) {
        alert("Network error. Please check your connection.");
    }
});

document.getElementById("chat-input").addEventListener("keypress", e => {
    if (e.key === "Enter") {
        e.preventDefault();
        document.getElementById("send-chat-btn").click();
    }
});

// Initial loading
loadResources();
loadChat();
refreshJoinButton();

// Auto-refresh
setInterval(loadChat, 5000);
setInterval(refreshJoinButton, 10000);
</script>

<?php include_once("includes/footer2.php"); ?>
<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
<?php include_once("includes/js-links-inc.php"); ?>

</body>
</html>
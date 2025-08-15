<?php
session_start();
require_once 'includes/db-conn.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['student_id'];

// Fetch student info
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Zoom Meetings - Edulk</title>
<?php include_once("includes/css-links-inc.php"); ?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
<style>
body { 
    background: #f5f7fa; 
    font-family: 'Inter', sans-serif; 
    margin: 0; 
    padding: 0; 
}

.section-title { 
    margin-top: 25px; 
    font-size: 1.4rem; 
    font-weight: 700; 
    color: #222; 
    border-left: 4px solid #0d6efd; 
    padding-left: 10px; 
}

.card-container { 
    display: grid; 
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
    width: 40%;
    gap: 20px; 
    margin-top: 15px; 
}

.meeting-card { 
    background: #fff; 
    border-radius: 16px; 
    padding: 20px; 
    box-shadow: 0 6px 20px rgba(0,0,0,0.08); 
    transition: transform 0.3s, box-shadow 0.3s, background 0.3s; 
    display: flex; 
    flex-direction: column; 
    justify-content: space-between; 
    cursor: pointer; 
    position: relative;
}

.meeting-card:hover { 
    transform: translateY(-6px); 
    box-shadow: 0 12px 28px rgba(0,0,0,0.18); 
    background: #f8faff;
}

.meeting-title { 
    font-size: 1.25rem; 
    font-weight: 700; 
    margin-bottom: 10px; 
    display: flex; 
    align-items: center; 
    gap: 10px; 
}

.meeting-title i { 
    color: #0d6efd; 
    font-size: 1.2rem; 
}

.meeting-sub, .meeting-time { 
    color: #555; 
    font-size: 0.95rem; 
    margin-bottom: 8px; 
    display: flex; 
    align-items: center; 
    gap: 8px; 
}

.meeting-sub i, .meeting-time i { 
    color: #0d6efd; 
}

.join-btn { 
    background: linear-gradient(90deg, #0d6efd, #084298); 
    color: #fff; 
    border: none; 
    border-radius: 10px; 
    padding: 10px 18px; 
    font-weight: 600; 
    text-align: center; 
    text-decoration: none; 
    display: inline-flex; 
    align-items: center; 
    gap: 8px; 
    justify-content: center;
    transition: all 0.3s; 
    margin-top: 12px; 
}

.join-btn:hover { 
    background: linear-gradient(90deg, #084298, #0d6efd); 
    transform: scale(1.05); 
    text-decoration: none; 
}

.badge-live, .badge-upcoming { 
    font-size: 0.75rem; 
    font-weight: 600; 
    padding: 3px 8px; 
    border-radius: 8px; 
    position: absolute; 
    top: 20px; 
    right: 20px; 
    box-shadow: 0 2px 6px rgba(0,0,0,0.1); 
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.badge-live { 
    background: #dc3545; 
    color: #fff; 
}

.badge-upcoming { 
    background: #ffc107; 
    color: #212529; 
}

@media (max-width: 480px) {
    .meeting-card { padding: 15px; }
    .meeting-title { font-size: 1.1rem; }
    .join-btn { padding: 8px 14px; font-size: 0.9rem; }
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
    <div class="card-body">
        <div class="section-title">Live Meetings Now</div>
        <div class="card-container" id="liveMeetingsContainer">
            Loading live meetings...
        </div>

        <div class="section-title">Upcoming Meetings</div>
        <div class="card-container" id="upcomingMeetingsContainer">
            Loading upcoming meetings...
        </div>
    </div>
</div>
</main>

<?php include_once("includes/footer.php") ?>
<?php include_once("includes/js-links-inc.php") ?>

<script>
async function loadMeetings() {
    const liveContainer = document.getElementById("liveMeetingsContainer");
    const upcomingContainer = document.getElementById("upcomingMeetingsContainer");
    liveContainer.innerHTML = "Loading live meetings...";
    upcomingContainer.innerHTML = "Loading upcoming meetings...";
    
    try {
        const res = await fetch("get_meetings.php");
        const meetings = await res.json();
        liveContainer.innerHTML = "";
        upcomingContainer.innerHTML = "";

        const liveMeetings = meetings.filter(m => m.status === "active" && parseInt(m.is_live) === 1);
        const upcomingMeetings = meetings.filter(m => m.status === "active" && parseInt(m.is_live) === 0);

        if(liveMeetings.length === 0) {
            liveContainer.innerHTML = "<p>No live meetings right now.</p>";
        } else {
            liveMeetings.forEach(m => {
                const card = document.createElement("div");
                card.className = "meeting-card";
                card.innerHTML = `
                    <div class="meeting-title">
                        <i class="fa-solid fa-video"></i>
                        ${m.title}
                        <span class="badge-live">LIVE</span>
                    </div>
                    <div class="meeting-sub"><i class="fa-solid fa-book"></i> ${m.subject ?? "-"}</div>
                    <div class="meeting-time"><i class="fa-solid fa-calendar-days"></i> ${m.date} &nbsp; <i class="fa-solid fa-clock"></i> ${m.start_time}</div>
                  <div class="meeting-time"><i class="bi bi-person-fill"></i> ${m.created_by} &nbsp; <i class="bi bi-briefcase-fill"></i> ${m.role}</div>
                    <a href="meeting_detail.php?id=${m.id}" class="join-btn"><i class="fa-solid fa-arrow-right-to-bracket"></i> Visit</a>
                `;
                liveContainer.appendChild(card);
            });
        }

        if(upcomingMeetings.length === 0) {
            upcomingContainer.innerHTML = "<p>No upcoming meetings.</p>";
        } else {
            upcomingMeetings.forEach(m => {
                const card = document.createElement("div");
                card.className = "meeting-card";
                card.innerHTML = `
                    <div class="meeting-title">
                        <i class="fa-solid fa-calendar-plus"></i>
                        ${m.title}
                        <span class="badge-upcoming">UPCOMING</span>
                    </div>
                    <div class="meeting-sub"><i class="fa-solid fa-book"></i> ${m.subject ?? "-"}</div>
                    <div class="meeting-time"><i class="fa-solid fa-calendar-days"></i> ${m.date} &nbsp; <i class="fa-solid fa-clock"></i> ${m.start_time}</div>
                  <div class="meeting-time"><i class="bi bi-person-fill"></i> ${m.created_by} &nbsp; <i class="bi bi-briefcase-fill"></i> ${m.role}</div>
                    <a href="meeting_detail.php?id=${m.id}" class="join-btn"><i class="fa-solid fa-eye"></i> View</a>
                `;
                upcomingContainer.appendChild(card);
            });
        }

    } catch(e) {
        liveContainer.innerHTML = "<p>Error loading live meetings.</p>";
        upcomingContainer.innerHTML = "<p>Error loading upcoming meetings.</p>";
        console.error(e);
    }
}

// Auto-refresh every 30 seconds
loadMeetings();
setInterval(loadMeetings, 30000);
</script>
</body>
</html>

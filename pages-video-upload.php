<?php
session_start();
require_once 'includes/db-conn.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['student_id'];
$sql = "SELECT name, email, nic, mobile, profile_picture FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch all active subjects with recordings
$subjectList = [];
$subjectQuery = $conn->query("
    SELECT s.id, s.name, COUNT(r.id) AS recording_count 
    FROM subjects s
    LEFT JOIN recordings r ON s.id = r.subject_id AND r.status = 'active'
    GROUP BY s.id
    ORDER BY s.name
");
while ($row = $subjectQuery->fetch_assoc()) {
    $subjectList[] = $row;
}

// Get selected subject from query parameter
$selected_subject = isset($_GET['subject']) ? intval($_GET['subject']) : null;

// Fetch recordings based on selection
$recordings = [];
if ($selected_subject) {
    $recordingQuery = $conn->prepare("
        SELECT r.*, s.name AS subject_name,
        CASE 
            WHEN r.role = 'superadmin' THEN sa.name
            WHEN r.role = 'lecture' THEN l.name
            ELSE 'Admin'
        END AS uploader_name
        FROM recordings r
        JOIN subjects s ON r.subject_id = s.id
        LEFT JOIN sadmins sa ON r.created_by = sa.id AND r.role = 'superadmin'
        LEFT JOIN lectures l ON r.created_by = l.id AND r.role = 'lecture'
        WHERE r.subject_id = ? AND r.status = 'active'
        ORDER BY r.release_time DESC
    ");
    $recordingQuery->bind_param("i", $selected_subject);
} else {
    $recordingQuery = $conn->prepare("
        SELECT r.*, s.name AS subject_name,
        CASE 
            WHEN r.role = 'superadmin' THEN sa.name
            WHEN r.role = 'lecture' THEN l.name
            ELSE 'Admin'
        END AS uploader_name
        FROM recordings r
        JOIN subjects s ON r.subject_id = s.id
        LEFT JOIN sadmins sa ON r.created_by = sa.id AND r.role = 'superadmin'
        LEFT JOIN lectures l ON r.created_by = l.id AND r.role = 'lecture'
        WHERE r.status = 'active'
        ORDER BY r.release_time DESC
        LIMIT 12
    ");
}

$recordingQuery->execute();
$result = $recordingQuery->get_result();
while ($row = $result->fetch_assoc()) {
    $recordings[] = $row;
}
$recordingQuery->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lecture Recordings - Edulk</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #6c757d;
            --light: #f8f9fa;
            --dark: #343a40;
            --card-shadow: 0 12px 30px rgba(0,0,0,0.08);
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
            padding: 20px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .page-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .subject-filter {
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            padding: 10px 20px;
            border-radius: 30px;
            box-shadow: var(--card-shadow);
        }

        .subject-filter select {
            border: none;
            background: transparent;
            font-size: 1rem;
            padding: 5px;
            min-width: 200px;
            outline: none;
        }

        .subject-filter i {
            color: var(--primary);
        }

        .subject-cards {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .subject-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 20px;
            text-align: center;
            flex: 1 1 200px;
            transition: var(--transition);
            cursor: pointer;
            border: 2px solid transparent;
        }

        .subject-card:hover, .subject-card.active {
            transform: translateY(-5px);
            border-color: var(--primary);
        }

        .subject-card.active {
            background: rgba(67, 97, 238, 0.05);
        }

        .subject-card i {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .subject-card h3 {
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .subject-card .count {
            font-size: 0.9rem;
            color: var(--secondary);
        }

        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .video-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
        }

        .video-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.12);
        }

        .video-thumbnail {
            position: relative;
            height: 180px;
            overflow: hidden;
            cursor: pointer;
        }

        .video-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .video-card:hover .video-thumbnail img {
            transform: scale(1.05);
        }

        .play-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.8;
            transition: var(--transition);
        }

        .video-card:hover .play-icon {
            opacity: 1;
            background: rgba(255, 255, 255, 0.95);
        }

        .play-icon i {
            font-size: 24px;
            color: var(--primary);
            margin-left: 5px;
        }

        .video-duration {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }

        .video-info {
            padding: 20px;
        }

        .subject-label {
            display: inline-block;
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
            padding: 3px 10px;
            border-radius: 30px;
            font-size: 0.8rem;
            margin-bottom: 10px;
        }

        .video-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .video-meta {
            display: flex;
            justify-content: space-between;
            color: var(--secondary);
            font-size: 0.85rem;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .video-meta div {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .resource-section {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .resource-title {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--dark);
        }

        .resource-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .resource-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            background: #f9f9ff;
            border-radius: 6px;
            font-size: 0.85rem;
            transition: var(--transition);
        }

        .resource-item:hover {
            background: #edf1ff;
        }

        .resource-item i {
            color: var(--primary);
            width: 20px;
            text-align: center;
        }

        .resource-name {
            flex-grow: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .no-recordings {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px;
            color: var(--secondary);
        }

        .no-recordings i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #d1d5e0;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .subject-cards {
                gap: 15px;
            }
            
            .video-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media (max-width: 480px) {
            .video-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include_once("includes/header.php") ?>
    <?php include_once("includes/student-sidebar.php") ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Lecture Recordings</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Lecture Recordings</li>
                </ol>
            </nav>
        </div>

        <div class="container">
            <div class="page-header">
                <h2 class="page-title">
                    <i class="fa-solid fa-video"></i>
                    Available Lecture Recordings
                </h2>
                <div class="subject-filter">
                    <i class="fa-solid fa-filter"></i>
                    <select id="subjectSelect" onchange="filterBySubject(this.value)">
                        <option value="">All Subjects</option>
                        <?php foreach ($subjectList as $subject): ?>
                            <option value="<?= $subject['id'] ?>" <?= $selected_subject == $subject['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($subject['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="subject-cards">
                <div class="subject-card <?= !$selected_subject ? 'active' : '' ?>" onclick="filterBySubject('')">
                    <i class="fa-solid fa-globe"></i>
                    <h3>All Subjects</h3>
                    <div class="count"><?= count($recordings) ?> Recordings</div>
                </div>
                
                <?php foreach ($subjectList as $subject): ?>
                    <div class="subject-card <?= $selected_subject == $subject['id'] ? 'active' : '' ?>" 
                         onclick="filterBySubject(<?= $subject['id'] ?>)">
                        <i class="fa-solid fa-book"></i>
                        <h3><?= htmlspecialchars($subject['name']) ?></h3>
                        <div class="count"><?= $subject['recording_count'] ?> Recordings</div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="video-grid">
                <?php if (count($recordings) > 0): ?>
                    <?php foreach ($recordings as $recording): ?>
                        <div class="video-card">
                            <div class="video-thumbnail" onclick="openVideoModal('<?= htmlspecialchars($recording['video_path']) ?>', <?= $recording['id'] ?>)">
                                <?php if (!empty($recording['thumbnail_path'])): ?>
                                    <img src="<?= htmlspecialchars($recording['thumbnail_path']) ?>" alt="Video thumbnail">
                                <?php else: ?>
                                    <div style="background: #e0e7ff; height: 100%; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa-solid fa-video" style="font-size: 3rem; color: var(--primary);"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="play-icon">
                                    <i class="fa-solid fa-play"></i>
                                </div>
                                <div class="video-duration">45:22</div>
                            </div>
                            <div class="video-info">
                                <span class="subject-label"><?= htmlspecialchars($recording['subject_name']) ?></span>
                                <h3 class="video-title"><?= htmlspecialchars($recording['title']) ?></h3>
                                <p class="text-muted" style="font-size: 0.9rem; margin-bottom: 5px;">
                                    <?= date("M d, Y", strtotime($recording['release_time'])) ?>
                                </p>
                                <p style="font-size: 0.9rem; color: #555; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?= htmlspecialchars($recording['description'] ?? 'No description available') ?>
                                </p>
                                
                                <div class="resource-section">
                                    <div class="resource-title">Resources:</div>
                                    <div class="resource-list">
                                        <?php
                                        $resources = $conn->prepare("SELECT * FROM recording_resources WHERE recording_id = ? AND status = 'active'");
                                        $resources->bind_param("i", $recording['id']);
                                        $resources->execute();
                                        $resResult = $resources->get_result();
                                        ?>
                                        
                                        <?php if ($resResult->num_rows > 0): ?>
                                            <?php while ($res = $resResult->fetch_assoc()): ?>
                                                <a href="<?= $res['type'] === 'file' ? htmlspecialchars($res['file_path']) : htmlspecialchars($res['link_url']) ?>" 
                                                   class="resource-item" 
                                                   target="_blank"
                                                   download="<?= $res['type'] === 'file' ? 'download' : '' ?>">
                                                    <i class="<?= $res['type'] === 'file' ? 'fa-solid fa-file' : 'fa-solid fa-link' ?>"></i>
                                                    <span class="resource-name"><?= htmlspecialchars($res['title']) ?></span>
                                                    <i class="fa-solid fa-<?= $res['type'] === 'file' ? 'download' : 'external-link-alt' ?>"></i>
                                                </a>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <div class="resource-item">
                                                <i class="fa-solid fa-info-circle"></i>
                                                <span>No resources available</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="video-meta">
                                    <div>
                                        <i class="fa-solid fa-user"></i>
                                        <?= htmlspecialchars($recording['uploader_name']) ?>
                                    </div>
                                    <div>
                                        <i class="fa-solid fa-eye"></i>
                                        <?= intval($recording['play_count']) ?> views
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-recordings">
                        <i class="fa-solid fa-video-slash"></i>
                        <h3>No Recordings Available</h3>
                        <p>There are currently no lecture recordings for this subject.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <div class="modal fade" id="videoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Lecture Recording</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <video id="modalVideo" controls style="width: 100%;" preload="metadata"></video>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="downloadBtn">
                        <i class="fa-solid fa-download me-1"></i> Download Video
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include_once("includes/footer2.php"); ?>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
    <?php include_once("includes/js-links-inc.php"); ?>

    <script>
        function filterBySubject(subjectId) {
            if (subjectId) {
                window.location.href = 'lecture-recordings.php?subject=' + subjectId;
            } else {
                window.location.href = 'lecture-recordings.php';
            }
        }
        
        function openVideoModal(videoPath, recordingId) {
            const modalVideo = document.getElementById('modalVideo');
            const modal = new bootstrap.Modal(document.getElementById('videoModal'));
            
            // Extract filename from path
            const filename = videoPath.split('/').pop();
            modalVideo.src = 'stream-video.php?file=' + encodeURIComponent(filename);
            
            // Update download button
            document.getElementById('downloadBtn').onclick = function() {
                downloadVideo(videoPath, recordingId);
            };
            
            modal.show();
            
            // Update play count
            updatePlayCount(recordingId);
            
            // When video ends, update play count again
            modalVideo.onended = function() {
                updatePlayCount(recordingId);
            };
        }
        
        function updatePlayCount(recordingId) {
            fetch('update-play-count.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: recordingId })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.error) {
                    // Update view count on the card
                    const card = document.querySelector(`.video-card [data-recording="${recordingId}"]`);
                    if (card) {
                        card.textContent = data.count + ' views';
                    }
                }
            });
        }
        
        function downloadVideo(videoPath, recordingId) {
            const filename = videoPath.split('/').pop();
            const link = document.createElement('a');
            link.href = 'stream-video.php?file=' + encodeURIComponent(filename);
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Update download count
            fetch('update-download-count.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: recordingId })
            });
        }
    </script>
</body>
</html>
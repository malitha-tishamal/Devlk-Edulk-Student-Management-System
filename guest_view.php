<?php
session_start();
require_once 'includes/db-conn.php';

$timeout_duration = 5 * 60; // 5 minutes

if (!isset($_SESSION['student_id'])) {
    if (!isset($_SESSION['guest_start_time'])) {
        $_SESSION['guest_start_time'] = time();
    } else {
        if ((time() - $_SESSION['guest_start_time']) > $timeout_duration) {
            session_unset();
            session_destroy();
            header("Location: index.php?msg=Guest session expired. Please login.");
            exit;
        }
    }
} else {
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
        session_unset();
        session_destroy();
        header("Location: index.php?msg=Session expired. Please login again.");
        exit;
    }
    $_SESSION['LAST_ACTIVITY'] = time();
}

$user_id = $_SESSION['student_id'] ?? null;

function getDeviceType($user_agent) {
    if (preg_match('/mobile/i', $user_agent)) return 'Mobile';
    if (preg_match('/tablet/i', $user_agent)) return 'Tablet';
    if (preg_match('/windows|macintosh|linux/i', $user_agent)) return 'Desktop';
    return 'Unknown';
}
function getOS($user_agent) {
    $os_array = [
        '/windows nt 10/i' => 'Windows 10',
        '/windows nt 6.3/i' => 'Windows 8.1',
        '/windows nt 6.1/i' => 'Windows 7',
        '/macintosh|mac os x/i' => 'Mac OS X',
        '/linux/i' => 'Linux',
        '/android/i' => 'Android',
        '/iphone/i' => 'iOS',
    ];
    foreach ($os_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) return $value;
    }
    return 'Unknown OS';
}
function getBrowser($user_agent) {
    $browser_array = [
        '/msie/i' => 'Internet Explorer',
        '/firefox/i' => 'Firefox',
        '/chrome/i' => 'Chrome',
        '/safari/i' => 'Safari',
        '/edge/i' => 'Edge',
        '/opera/i' => 'Opera',
    ];
    foreach ($browser_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) return $value;
    }
    return 'Unknown Browser';
}

$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$device_type = getDeviceType($user_agent);
$os = getOS($user_agent);
$browser = getBrowser($user_agent);
$language = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'Unknown';
$referrer = $_SERVER['HTTP_REFERER'] ?? 'Direct';
$current_url = $_SERVER['REQUEST_URI'] ?? '';
$session_id = session_id();
$timestamp = date('Y-m-d H:i:s');

$logStmt = $conn->prepare("INSERT INTO user_logs (user_id, ip_address, user_agent, device_type, os, browser, language, referrer, current_url, session_id, accessed_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$logStmt->bind_param("issssssssss", $user_id, $ip, $user_agent, $device_type, $os, $browser, $language, $referrer, $current_url, $session_id, $timestamp);
$logStmt->execute();
$logStmt->close();

$selectedSemester = $_GET['semester'] ?? '1';
$selectedSubject = $_GET['subject'] ?? '';

$semesters = [];
$semQuery = $conn->query("SELECT DISTINCT semester FROM subjects ORDER BY semester ASC");
while ($row = $semQuery->fetch_assoc()) {
    $semesters[] = $row['semester'];
}

$subjects = [];
if ($selectedSemester !== '') {
    if (!empty($selectedSubject)) {
        // If subject selected, load only that subject
        $subjectQuery = $conn->prepare("SELECT id, name FROM subjects WHERE id = ?");
        $subjectQuery->bind_param("i", $selectedSubject);
        $subjectQuery->execute();
        $resultSubjects = $subjectQuery->get_result();
        while ($row = $resultSubjects->fetch_assoc()) {
            $subjects[] = $row;
        }
        $subjectQuery->close();
    } else {
        // Else load all subjects of the semester
        $subjectQuery = $conn->prepare("SELECT id, name FROM subjects WHERE semester = ? ORDER BY name ASC");
        $subjectQuery->bind_param("s", $selectedSemester);
        $subjectQuery->execute();
        $resultSubjects = $subjectQuery->get_result();
        while ($row = $resultSubjects->fetch_assoc()) {
            $subjects[] = $row;
        }
        $subjectQuery->close();
    }
}

function getFileIcon($ext) {
    switch (strtolower($ext)) {
        case 'pdf': return '<i class="fa-solid fa-file-pdf text-danger"></i>';
        case 'doc':
        case 'docx': return '<i class="fa-solid fa-file-word text-primary"></i>';
        case 'xls':
        case 'xlsx': return '<i class="fa-solid fa-file-excel text-success"></i>';
        case 'ppt':
        case 'pptx': return '<i class="fa-solid fa-file-powerpoint text-warning"></i>';
        default: return '<i class="fa-solid fa-file text-muted"></i>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Study Materials - EduWide</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php include_once("includes/css-links-inc.php"); ?>
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #eef1fd;
            --primary-dark: #3a56d4;
            --secondary: #6c757d;
            --light: #f8f9fa;
            --dark: #343a40;
            --success: #28a745;
            --warning: #ff9f1c;
            --card-shadow: 0 12px 30px rgba(0,0,0,0.08);
            --border-radius: 16px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f5f7ff 0%, #eef2ff 100%);
            font-family: 'Poppins', sans-serif;
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
            padding-bottom: 10px;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 4px;
            background: var(--primary);
            border-radius: 4px;
        }

        .filters-container {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
            background: white;
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .filters-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 8px;
            height: 100%;
            background: var(--primary);
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 250px;
            flex: 1;
            position: relative;
        }

        .filter-label {
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.1rem;
        }

        .filter-select {
            padding: 14px 18px;
            border: 2px solid #e0e7ff;
            border-radius: 12px;
            font-size: 1.05rem;
            transition: var(--transition);
            background: #f8f9ff;
            color: #4b5563;
            font-weight: 500;
            cursor: pointer;
            appearance: none;
            padding-right: 50px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.15);
        }

        /* Custom dropdown arrow */
        .filter-group::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 20px;
            bottom: 16px;
            font-size: 1.2rem;
            color: var(--primary);
            pointer-events: none;
        }

        .subject-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 30px;
            margin-bottom: 35px;
            transition: var(--transition);
            border: 1px solid rgba(67, 97, 238, 0.1);
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: translateY(20px);
        }

        .subject-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), #8a4fff);
        }

        .subject-card.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .subject-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(67, 97, 238, 0.1);
        }

        .subject-name {
            font-size: 1.7rem;
            font-weight: 800;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .subject-icon {
            background: rgba(67, 97, 238, 0.1);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: var(--primary);
        }

        .week-section {
            margin-bottom: 35px;
            background: rgba(67, 97, 238, 0.03);
            border-radius: 12px;
            padding: 25px;
        }

        .week-title {
            font-weight: 700;
            color: white;
            margin-bottom: 25px;
            padding: 12px 20px;
            background: linear-gradient(90deg, var(--primary), #8a4fff);
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-size: 1.2rem;
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.2);
        }

        .files-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }

        .file-card {
            background: white;
            border-radius: 14px;
            padding: 25px;
            transition: var(--transition);
            border: 1px solid rgba(67, 97, 238, 0.1);
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            opacity: 0;
            transform: translateY(20px);
        }

        .file-card.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .file-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(67, 97, 238, 0.2);
            border-color: var(--primary);
        }

        .file-header {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .file-icon {
            font-size: 2.5rem;
            margin-right: 20px;
            color: var(--primary);
            flex-shrink: 0;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: rgba(67, 97, 238, 0.08);
        }

        .file-info {
            flex-grow: 1;
        }

        .file-title {
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--dark);
            font-size: 1.15rem;
            line-height: 1.4;
        }

        .file-meta {
            display: flex;
            gap: 15px;
            font-size: 0.95rem;
            color: var(--secondary);
            flex-wrap: wrap;
        }

        .file-category {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .file-extension {
            background: rgba(107, 114, 128, 0.1);
            color: #4b5563;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .file-actions {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .file-size {
            color: var(--secondary);
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .download-btn {
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }

        .download-btn:hover {
            background: linear-gradient(90deg, var(--primary-dark), #2a0ca3);
            color: white;
            text-decoration: none;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.4);
        }

        .download-btn.disabled {
            background: linear-gradient(90deg, #e0e0e0, #b0b0b0);
            cursor: not-allowed;
            box-shadow: none;
        }

        .download-btn.disabled:hover {
            transform: none;
        }

        .no-files {
            text-align: center;
            padding: 60px;
            color: var(--secondary);
            grid-column: 1 / -1;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
        }

        .no-files i {
            font-size: 4rem;
            color: #d1d5e0;
            margin-bottom: 20px;
            opacity: 0.7;
        }

        .no-files h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: var(--primary);
        }

        .no-files p {
            font-size: 1.1rem;
            max-width: 500px;
            margin: 0 auto;
        }

        .section-title {
            font-weight: 700;
            color: white;
            margin-bottom: 25px;
            padding: 12px 20px;
            background: linear-gradient(90deg, var(--warning), #ff7b00);
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-size: 1.2rem;
            box-shadow: 0 5px 15px rgba(255, 159, 28, 0.2);
        }

        .file-count-badge {
            background: var(--primary);
            color: white;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-left: 15px;
        }

        .subject-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 10px;
        }

        .subject-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--secondary);
            font-size: 0.95rem;
        }

        .loading-indicator {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: rgba(67, 97, 238, 0.2);
            z-index: 9999;
        }

        .loading-indicator::before {
            content: '';
            position: absolute;
            height: 4px;
            width: 50%;
            background: var(--primary);
            animation: loading 1.5s infinite ease-in-out;
        }

        @keyframes loading {
            0% { left: -50%; }
            100% { left: 100%; }
        }

        @media (max-width: 992px) {
            .files-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filters-container {
                flex-direction: column;
                gap: 20px;
                padding: 20px;
            }
            
            .filter-group {
                min-width: 100%;
            }
            
            .files-grid {
                grid-template-columns: 1fr;
            }
            
            .subject-name {
                font-size: 1.5rem;
            }
            
            .week-section, .subject-card {
                padding: 20px;
            }
            
            .page-title {
                font-size: 1.7rem;
            }
        }

        @media (max-width: 480px) {
            .subject-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .subject-name {
                font-size: 1.4rem;
            }
            
            .download-btn {
                padding: 10px 20px;
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
<?php include_once("includes/guest-header.php") ?>
<?php include_once("includes/guest-sidebar.php") ?>

<!-- Loading indicator for filter changes -->
<div class="loading-indicator" id="loadingIndicator"></div>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Study Materials</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Study Materials</li>
            </ol>
        </nav>
    </div>

    <div class="card mb-4 shadow-sm" style="border-radius: var(--border-radius); overflow: hidden;">
        <div class="card-body" style="padding: 30px;">
            <div class="page-header">
                <h2 class="page-title">
                    <i class="fa-solid fa-file-circle-check"></i>
                    Available Study Materials
                </h2>
                <form id="filterForm" method="GET" class="filters-container">
                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="fa-solid fa-graduation-cap"></i> Filter by Semester
                        </label>
                        <select id="semester" name="semester" class="filter-select">
                            <?php foreach ($semesters as $sem): ?>
                                <option value="<?= htmlspecialchars($sem) ?>" <?= $sem == $selectedSemester ? 'selected' : '' ?>>
                                    Semester <?= htmlspecialchars($sem) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="fa-solid fa-book-open"></i> Filter by Subject
                        </label>
                        <select id="subject" name="subject" class="filter-select">
                            <option value="">All Subjects</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?= htmlspecialchars($subject['id']) ?>" <?= $subject['id'] == $selectedSubject ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subject['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>

            <?php if (empty($subjects)): ?>
                <div class="no-files">
                    <i class="fa-solid fa-folder-open"></i>
                    <h3>No Study Materials Found</h3>
                    <p>Select a semester to view available study materials</p>
                    <div class="mt-4">
                        <button class="download-btn" onclick="location.reload()">
                            <i class="fa-solid fa-rotate"></i> Refresh Page
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div id="materialsContainer">
                    <?php foreach ($subjects as $subj): ?>
                        <div class="subject-card">
                            <div class="subject-header">
                                <div>
                                    <h3 class="subject-name">
                                        <span class="subject-icon">
                                            <i class="fa-solid fa-book"></i>
                                        </span>
                                        <?= htmlspecialchars($subj['name']) ?>
                                    </h3>
                                    <div class="subject-meta">
                                        <span class="subject-meta-item">
                                            <i class="fa-solid fa-layer-group"></i>
                                            Semester <?= htmlspecialchars($selectedSemester) ?>
                                        </span>
                                        <span class="subject-meta-item">
                                            <i class="fa-solid fa-clock"></i>
                                            Updated Recently
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <?php
                            // Fetch files for this subject
                            $stmt = $conn->prepare("SELECT title, category, filename FROM tuition_files WHERE subject_id = ? AND status = 'active' ORDER BY uploaded_at DESC");
                            $stmt->bind_param("i", $subj['id']);
                            $stmt->execute();
                            $files = $stmt->get_result();
                            $stmt->close();
                            
                            $notesByWeek = [];
                            $otherFiles = [];
                            
                            while ($file = $files->fetch_assoc()) {
                                if (strtolower($file['category']) === 'notes' && preg_match('/^(Week\s*\d+)/i', $file['title'], $matches)) {
                                    $weekGroup = $matches[1]; // e.g. "Week 1"
                                    $notesByWeek[$weekGroup][] = $file;
                                } else {
                                    $otherFiles[] = $file;
                                }
                            }
                            ?>
                            
                            <?php if (!empty($notesByWeek)): ?>
                                <?php ksort($notesByWeek, SORT_NATURAL | SORT_FLAG_CASE); ?>
                                <?php foreach ($notesByWeek as $week => $filesWeek): ?>
                                    <div class="week-section">
                                        <h4 class="week-title">
                                            <i class="fa-solid fa-calendar-week"></i>
                                            <?= htmlspecialchars($week) ?>
                                            <span class="file-count-badge"><?= count($filesWeek) ?> files</span>
                                        </h4>
                                        
                                        <div class="files-grid">
                                            <?php foreach ($filesWeek as $nf): ?>
                                                <?php
                                                $filename = htmlspecialchars($nf['filename']);
                                                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                                                $fileIcon = getFileIcon($extension);
                                                $downloadLink = $user_id ? '../uploads/'.urlencode($filename) : '#';
                                                $onclick = $user_id ? '' : 'onclick="alert(\'Please login to download files.\'); return false;"';
                                                ?>
                                                
                                                <div class="file-card">
                                                    <div class="file-header">
                                                        <div class="file-icon">
                                                            <?= $fileIcon ?>
                                                        </div>
                                                        <div class="file-info">
                                                            <div class="file-title">
                                                                <?= htmlspecialchars($nf['title']) ?>
                                                            </div>
                                                            <div class="file-meta">
                                                                <span class="file-category">
                                                                    <?= htmlspecialchars($nf['category']) ?>
                                                                </span>
                                                                <span class="file-extension">
                                                                    .<?= strtoupper($extension) ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="file-actions">
                                                        <span class="file-size">
                                                            <i class="fa-solid fa-database"></i> 2.4MB
                                                        </span>
                                                        <a href="<?= $downloadLink ?>" <?= $onclick ?> class="download-btn <?= $user_id ? '' : 'disabled' ?>">
                                                            <i class="fa-solid fa-download"></i> Download
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <?php if (!empty($otherFiles)): ?>
                                <div class="other-section mt-5">
                                    <h4 class="section-title">
                                        <i class="fa-solid fa-file-circle-plus"></i>
                                        Additional Materials
                                        <span class="file-count-badge"><?= count($otherFiles) ?> files</span>
                                    </h4>
                                    
                                    <div class="files-grid">
                                        <?php foreach ($otherFiles as $of): ?>
                                            <?php
                                            $filename = htmlspecialchars($of['filename']);
                                            $extension = pathinfo($filename, PATHINFO_EXTENSION);
                                            $fileIcon = getFileIcon($extension);
                                            $downloadLink = $user_id ? '../uploads/'.urlencode($filename) : '#';
                                            $onclick = $user_id ? '' : 'onclick="alert(\'Please login to download files.\'); return false;"';
                                            ?>
                                            
                                            <div class="file-card">
                                                <div class="file-header">
                                                    <div class="file-icon">
                                                        <?= $fileIcon ?>
                                                    </div>
                                                    <div class="file-info">
                                                        <div class="file-title">
                                                            <?= htmlspecialchars($of['title']) ?>
                                                        </div>
                                                        <div class="file-meta">
                                                            <span class="file-category">
                                                                <?= htmlspecialchars($of['category']) ?>
                                                            </span>
                                                            <span class="file-extension">
                                                                .<?= strtoupper($extension) ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="file-actions">
                                                    <span class="file-size">
                                                        <i class="fa-solid fa-database"></i> 1.8MB
                                                    </span>
                                                    <a href="<?= $downloadLink ?>" <?= $onclick ?> class="download-btn <?= $user_id ? '' : 'disabled' ?>">
                                                        <i class="fa-solid fa-download"></i> Download
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (empty($notesByWeek) && empty($otherFiles)): ?>
                                <div class="no-files" style="padding: 30px; margin-top: 20px;">
                                    <i class="fa-solid fa-file-circle-xmark"></i>
                                    <h3>No Materials Available</h3>
                                    <p>This subject doesn't have any study materials yet</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include_once("includes/footer.php") ?>
<?php include_once("includes/js-links-inc.php") ?>

<script>
    // Auto-submit form when filters change
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('filterForm');
        const semesterSelect = document.getElementById('semester');
        const subjectSelect = document.getElementById('subject');
        const loadingIndicator = document.getElementById('loadingIndicator');
        
        // Function to submit form
        function submitFilterForm() {
            loadingIndicator.style.display = 'block';
            filterForm.submit();
        }
        
        // Event listeners for filter changes
        semesterSelect.addEventListener('change', submitFilterForm);
        subjectSelect.addEventListener('change', submitFilterForm);
        
        // Add animations to cards as they appear
        const animateCards = () => {
            const cards = document.querySelectorAll('.subject-card, .file-card');
            
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('visible');
                }, 100 + (index * 50));
            });
        };
        
        // Run animations after page load
        setTimeout(animateCards, 300);
    });
</script>
</body>
</html>

<?php $conn->close(); ?>
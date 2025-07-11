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
    <title>View Files - EduWide</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <?php include_once("includes/css-links-inc.php"); ?>
    <style>
        .subject-heading {
            font-weight: bold;
            color: #007bff;
            margin-top: 30px;
        }
        .week-subheading {
            font-weight: 600;
            color: #1a73e8;
            margin-left: 20px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<?php include_once("includes/guest-header.php") ?>
<?php include_once("includes/guest-sidebar.php") ?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Available Files</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Available Files</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="mb-4 row g-3">
                    <div class="col-md-4">
                        <label for="semester">Select Semester:</label>
                        <select id="semester" name="semester" class="form-select" onchange="this.form.submit()">
                            <?php foreach ($semesters as $sem): ?>
                                <option value="<?= htmlspecialchars($sem) ?>" <?= $sem == $selectedSemester ? 'selected' : '' ?>>
                                    Semester <?= htmlspecialchars($sem) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="subject">Select Subject:</label>
                        <select id="subject" name="subject" class="form-select" onchange="this.form.submit()">
                            <option value="">-- All Subjects --</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?= htmlspecialchars($subject['id']) ?>" <?= $subject['id'] == $selectedSubject ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subject['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>

                <?php
                if (empty($subjects)) {
                    echo "<p>No subjects found for the selected semester.</p>";
                } else {
                    foreach ($subjects as $subj) {
                        echo "<h5 class='subject-heading'>" . htmlspecialchars($subj['name']) . "</h5>";

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

                        // Output Notes grouped by Week
                        if (!empty($notesByWeek)) {
                            ksort($notesByWeek, SORT_NATURAL | SORT_FLAG_CASE);
                            foreach ($notesByWeek as $week => $filesWeek) {
                                echo "<h6 class='week-subheading'>" . htmlspecialchars($week) . "</h6>";
                                echo '<table class="table table-bordered mb-4"><thead><tr><th>Notes</th><th>Category</th><th>Extension</th><th>Download</th></tr></thead><tbody>';
                                foreach ($filesWeek as $nf) {
                                    $filename = htmlspecialchars($nf['filename']);
                                    $extension = pathinfo($filename, PATHINFO_EXTENSION);
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($nf['title']) . '</td>';
                                    echo '<td>' . htmlspecialchars($nf['category']) . '</td>';
                                    echo '<td>' . getFileIcon($extension) . ' ' . strtoupper($extension) . '</td>';
                                    if ($user_id) {
                                        echo '<td><a href="../uploads/' . urlencode($filename) . '" target="_blank" rel="noopener">Download</a></td>';
                                    } else {
                                        echo '<td><a href="#" onclick="alert(\'Please login to download files.\'); return false;">Download</a></td>';
                                    }
                                    echo '</tr>';
                                }
                                echo '</tbody></table>';
                            }
                        }

                        // Output other files
                        if (!empty($otherFiles)) {
                            echo '<table class="table table-bordered mb-4"><thead><tr><th>Notes</th><th>Category</th><th>Extension</th><th>Download</th></tr></thead><tbody>';
                            foreach ($otherFiles as $of) {
                                $filename = htmlspecialchars($of['filename']);
                                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($of['title']) . '</td>';
                                echo '<td>' . htmlspecialchars($of['category']) . '</td>';
                                echo '<td>' . getFileIcon($extension) . ' ' . strtoupper($extension) . '</td>';
                                if ($user_id) {
                                    echo '<td><a href="../uploads/' . urlencode($filename) . '" target="_blank" rel="noopener">Download</a></td>';
                                } else {
                                    echo '<td><a href="#" onclick="alert(\'Please login to download files.\'); return false;">Download</a></td>';
                                }
                                echo '</tr>';
                            }
                            echo '</tbody></table>';
                        }
                    }
                }
                ?>
            </div>
        </div>
    </section>
</main>

<?php include_once("includes/footer.php") ?>
<?php include_once("includes/js-links-inc.php") ?>
</body>
</html>

<?php $conn->close(); ?>

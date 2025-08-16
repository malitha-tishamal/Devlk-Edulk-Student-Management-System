<?php 
session_start();
require_once 'includes/db-conn.php';  


$user_id = $_SESSION['student_id'];

// Get student details
$sql = "SELECT * FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Get distinct semesters
$semesters = [];
$semesterQuery = $conn->query("SELECT DISTINCT semester FROM subjects ORDER BY semester");
while ($row = $semesterQuery->fetch_assoc()) {
    $semesters[] = $row['semester'];
}

// Get selected semester
$selected_semester = $_GET['semester'] ?? '';

// Fetch subjects for selected semester (count PUBLIC + PRIVATE recordings)
$subjectList = [];
$subjectQuery = $conn->prepare("
    SELECT s.*, 
           COUNT(r.id) AS recording_count 
    FROM subjects s
    LEFT JOIN recordings r 
        ON s.id = r.subject_id 
       AND r.status = 'active'
       AND (r.access_level = 'public')
    WHERE s.semester = ? OR ? = ''
    GROUP BY s.id
    ORDER BY s.code
");
$subjectQuery->bind_param("ss", $selected_semester, $selected_semester);
$subjectQuery->execute();
$subjectResult = $subjectQuery->get_result();
while ($row = $subjectResult->fetch_assoc()) {
    $subjectList[] = $row;
}
$subjectQuery->close();

// Get selected subject
$selected_subject = isset($_GET['subject']) ? intval($_GET['subject']) : null;

// Fetch recordings (PUBLIC + PRIVATE)
$recordings = [];
if ($selected_subject) {
    // When a subject is selected
    $recordingQuery = $conn->prepare("
        SELECT r.*, s.name AS subject_name, s.code AS subject_code,
        CASE 
            WHEN r.role = 'superadmin' THEN sa.name
            WHEN r.role = 'lecture' THEN l.name
            ELSE 'Admin'
        END AS uploader_name
        FROM recordings r
        JOIN subjects s ON r.subject_id = s.id
        LEFT JOIN sadmins sa ON r.created_by = sa.id AND r.role = 'superadmin'
        LEFT JOIN lectures l ON r.created_by = l.id AND r.role = 'lecture'
        WHERE r.subject_id = ? 
          AND r.status = 'active'
          AND (r.access_level = 'public')
        ORDER BY r.release_time DESC
    ");
    $recordingQuery->bind_param("i", $selected_subject);

} else {
    // Show latest 12 PUBLIC + PRIVATE recordings
    $recordingQuery = $conn->prepare("
        SELECT r.*, s.name AS subject_name, s.code AS subject_code,
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
          AND (r.access_level = 'public')
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
    <?php include_once("includes/css-links-inc.php"); ?>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #6c757d;
            --light: #f8f9fa;
            --dark: #343a40;
            --success: #28a745;
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

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .filters-container {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            background: white;
            padding: 10px 10px;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 250px;
        }

        .filter-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-select {
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            background: #f8f9ff;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .semester-cards {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .semester-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 10px 10px;
            transition: var(--transition);
            cursor: pointer;
            border: 2px solid transparent;
            min-width: 150px;
            text-align: center;
        }

        .semester-card:hover, .semester-card.active {
            transform: translateY(-5px);
            border-color: var(--primary);
        }

        .semester-card.active {
            background: rgba(67, 97, 238, 0.05);
        }

        .semester-card h3 {
            font-size: 1.2rem;
            margin-bottom: 5px;
            color: var(--primary);
        }

        .semester-card .count {
            font-size: 0.9rem;
            color: var(--secondary);
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
            padding: 15px;
            transition: var(--transition);
            cursor: pointer;
            border: 2px solid transparent;
            min-width: 250px;
                        text-align: center;
            flex: 1;
        }

        .subject-card:hover, .subject-card.active {
            transform: translateY(-5px);
            border-color: var(--primary);
        }

        .subject-card.active {
            background: rgba(67, 97, 238, 0.05);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.15);
        }

        .subject-card .code {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .subject-card .name {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--dark);
        }

        .subject-card .count {
            display: inline-block;
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .subject-card .count i {
            margin-right: 5px;
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

        .semester-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
            margin: 30px 0 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(67, 97, 238, 0.2);
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filters-container {
                width: 100%;
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
            
            .semester-cards {
                flex-direction: column;
            }
            
            .semester-card, .subject-card {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include_once("includes/header2.php") ?>
    <?php include_once("includes/guest-sidebar.php") ?>
    
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
        
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="page-header">
                    <h2 class="page-title">
                        <i class="fa-solid fa-video"></i>
                        Available Lecture Recordings
                    </h2>
                    <div class="filters-container">
                        <div class="filter-group">
                            <label class="filter-label">
                                <i class="fa-solid fa-filter"></i> Filter by Semester
                            </label>
                            <select class="filter-select" id="semesterSelect" onchange="filterBySemester(this.value)">
                                <option value="">All Semesters</option>
                                <?php foreach ($semesters as $semester): ?>
                                    <option value="<?= htmlspecialchars($semester) ?>" <?= $selected_semester === $semester ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($semester) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">
                                <i class="fa-solid fa-book"></i> Filter by Subject
                            </label>
                            <select class="filter-select" id="subjectSelect" onchange="filterBySubject(this.value)">
                                <option value="">All Subjects</option>
                                <?php foreach ($subjectList as $subject): ?>
                                    <option value="<?= $subject['id'] ?>" <?= $selected_subject == $subject['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($subject['code']) ?> - <?= htmlspecialchars($subject['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <?php if ($selected_semester): ?>
                    <h3 class="semester-title">
                        <i class="fa-solid fa-calendar"></i>
                        <?= htmlspecialchars($selected_semester) ?>
                    </h3>
                <?php endif; ?>
                
                <div class="semester-cards">
                    <?php foreach ($semesters as $semester): ?>
                        <div class="semester-card <?= $selected_semester === $semester ? 'active' : '' ?>" 
                             onclick="filterBySemester('<?= htmlspecialchars($semester) ?>')">
                            <h3><?= htmlspecialchars($semester) ?></h3>
                            <div class="count">
                                <?php 
                                $count = 0;
                                foreach ($subjectList as $subject) {
                                    if ($subject['semester'] === $semester) {
                                        $count++;
                                    }
                                }
                                echo $count . ' Subject' . ($count !== 1 ? 's' : '');
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="subject-cards">
                    <?php if (count($subjectList) > 0): ?>
                        <?php foreach ($subjectList as $subject): ?>
                            <div class="subject-card <?= $selected_subject == $subject['id'] ? 'active' : '' ?>" 
                                 onclick="window.location.href='subject-recordings-guest.php?subject=<?= $subject['id'] ?>'">
                                 <i class="fa-solid fa-book" style=" font-size: 60px; color: blue;"></i> 
                                <div class="code"><?= htmlspecialchars($subject['code']) ?></div>
                                <div class="name"><?= htmlspecialchars($subject['name']) ?></div>
                                <div class="count">
                                    <i class="fa-solid fa-video"></i>
                                    <?= intval($subject['recording_count']) ?> Recording<?= $subject['recording_count'] != 1 ? 's' : '' ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-subjects">
                            <i class="fa-solid fa-book-open-reader"></i>
                            <h3>No Subjects Found</h3>
                            <p>Select a semester to view available subjects</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                
        </div>
    </main>

    <script>
        function filterBySemester(semester) {
            const subjectId = "<?= $selected_subject ?>";
            let url = 'pages-lecture-recordings-guest.php?';
            
            if (semester) {
                url += 'semester=' + encodeURIComponent(semester);
            }
            
            // Keep subject filter if it exists and we're not changing the semester
            if (subjectId && semester === "<?= $selected_semester ?>") {
                url += '&subject=' + subjectId;
            }
            
            window.location.href = url;
        }
        
        function filterBySubject(subjectId) {
            const semester = "<?= $selected_semester ?>";
            let url = 'pages-lecture-recordings-guest.php?';
            
            if (semester) {
                url += 'semester=' + encodeURIComponent(semester) + '&';
            }
            
            if (subjectId) {
                url += 'subject=' + encodeURIComponent(subjectId);
            }
            
            window.location.href = url;
        }
        
    </script>

    <?php include_once("includes/footer.php"); ?>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
    <?php include_once("includes/js-links-inc.php"); ?>
</body>
</html>
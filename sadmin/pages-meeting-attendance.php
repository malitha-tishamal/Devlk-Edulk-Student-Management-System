<?php
session_start();
require_once '../includes/db-conn.php';

// Redirect if not logged in
if (!isset($_SESSION['sadmin_id'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch logged-in superadmin details
$user_id = $_SESSION['sadmin_id'];
$sql = "SELECT name, email, nic, mobile, profile_picture FROM sadmins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch all meetings
$meetings = [];
$res = $conn->query("SELECT id, title, date FROM meetings ORDER BY date DESC");
while ($row = $res->fetch_assoc()) $meetings[] = $row;

// Selected meeting & attendance
$selectedMeeting = $_GET['meeting'] ?? '';
$attendance = [];
$meetingDetails = null;
$attendanceStats = [
    'total' => 0,
    'present' => 0,
    'late' => 0,
    'absent' => 0
];

if ($selectedMeeting) {
    // ✅ Fetch Meeting Details
    $stmt = $conn->prepare("SELECT id, title, date, start_time, zoom_link, created_by, role, subject 
                            FROM meetings WHERE id = ?");
    $stmt->bind_param("i", $selectedMeeting);
    $stmt->execute();
    $meetingDetails = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // ✅ Fetch Attendance with Meeting Info
    $stmt = $conn->prepare("
        SELECT id, meeting_id, student_id, name, regno, logtime, status, ip_address, user_agent 
        FROM meeting_attendance 
        WHERE meeting_id = ? 
        ORDER BY logtime DESC
    ");
    $stmt->bind_param("i", $selectedMeeting);
    $stmt->execute();
    $attendance = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Calculate attendance statistics
    $attendanceStats['total'] = count($attendance);
    foreach ($attendance as $record) {
        if (strtolower($record['status']) == "present") {
            $attendanceStats['present']++;
        } elseif (strtolower($record['status']) == "late") {
            $attendanceStats['late']++;
        } else {
            $attendanceStats['absent']++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Meeting Attendance | Modern Dashboard</title>
  <?php include_once("../includes/css-links-inc.php"); ?>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --secondary: #3f37c9;
      --success: #4cc9f0;
      --info: #4895ef;
      --warning: #f72585;
      --danger: #e63946;
      --light: #f8f9fa;
      --dark: #212529;
      --sidebar-width: 250px;
      --header-height: 70px;
      --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      --transition: all 0.3s ease;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f5f7fb;
      color: #344767;
      overflow-x: hidden;
    }

    .main-container {
      margin-left: var(--sidebar-width);
      padding-top: calc(var(--header-height) + 20px);
      transition: var(--transition);
    }

    .card {
      border: none;
      border-radius: 12px;
      box-shadow: var(--card-shadow);
      margin-bottom: 24px;
      transition: var(--transition);
    }

    .card:hover {
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .card-title {
      font-weight: 600;
      color: var(--dark);
      font-size: 1.25rem;
    }

    .breadcrumb-item a {
      color: var(--primary);
      text-decoration: none;
    }

    .breadcrumb-item.active {
      color: #6c757d;
    }

    .form-select {
      border-radius: 8px;
      padding: 12px 16px;
      border: 1px solid #e2e8f0;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .form-select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
    }

    .meeting-info {
      background: linear-gradient(120deg, #f0f4ff, #e8eeff);
      padding: 1.5rem;
      border-radius: 12px;
      margin-bottom: 2rem;
      border-left: 4px solid var(--primary);
    }

    .meeting-info h5 {
      margin-bottom: .75rem;
      font-weight: 700;
      color: var(--primary);
      font-size: 1.4rem;
    }

    .meeting-info p {
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
    }

    .meeting-info b {
      min-width: 100px;
      display: inline-block;
      color: #4a5568;
    }

    .table th {
      background-color: var(--primary);
      color: white;
      font-weight: 600;
      padding: 12px 15px;
    }

    .table td {
      padding: 12px 15px;
      vertical-align: middle;
    }

    .badge {
      padding: 8px 12px;
      border-radius: 6px;
      font-weight: 500;
    }

    .btn-primary {
      background-color: var(--primary);
      border-color: var(--primary);
      border-radius: 8px;
      padding: 10px 20px;
      font-weight: 500;
    }

    .btn-primary:hover {
      background-color: var(--secondary);
      border-color: var(--secondary);
    }

    .pagetitle {
      margin-bottom: 1.5rem;
    }

    .pagetitle h1 {
      font-weight: 700;
      color: var(--dark);
      font-size: 1.8rem;
    }

    .stat-card {
      text-align: center;
      padding: 1.5rem;
      border-radius: 12px;
      background: white;
      box-shadow: var(--card-shadow);
      transition: var(--transition);
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
    }

    .stat-card i {
      font-size: 2rem;
      margin-bottom: 1rem;
    }

    .stat-card h3 {
      font-weight: 700;
      margin-bottom: 0.5rem;
      font-size: 1.8rem;
    }

    .stat-card p {
      color: #718096;
      margin-bottom: 0;
      font-weight: 500;
    }

    .dataTables_wrapper .btn {
      border-radius: 6px;
      margin: 0 3px;
    }

    .dt-buttons {
      margin-bottom: 15px;
    }

    .attendance-chart {
      height: 300px;
      margin-bottom: 2rem;
    }

    @media (max-width: 992px) {
      .main-container {
        margin-left: 0;
      }
    }
  </style>
</head>

<body>
<?php include_once("../includes/header.php"); ?>
<?php include_once("../includes/sadmin-sidebar.php"); ?>

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Meeting Attendance</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item">Meetings</li>
        <li class="breadcrumb-item active">Attendance</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-people-fill me-2"></i> Meeting Attendance</h5>

            <!-- Meeting Selection -->
            <form method="GET" class="mb-4">
              <label class="form-label fw-semibold">Select Meeting</label>
              <div class="row">
                <div class="col-md-6">
                  <select name="meeting" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Select Meeting --</option>
                    <?php foreach ($meetings as $m): ?>
                      <option value="<?= $m['id'] ?>" <?= ($m['id']==$selectedMeeting)?'selected':'' ?>>
                        <?= htmlspecialchars($m['title'])." (".date('M d, Y', strtotime($m['date'])).")" ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </form>

            <!-- Stats Cards -->
            <?php if ($selectedMeeting && $meetingDetails): ?>
            <div class="row mb-4">
              <div class="col-md-3">
                <div class="stat-card">
                  <i class="bi bi-people-fill text-primary"></i>
                  <h3><?= $attendanceStats['total'] ?></h3>
                  <p>Total Attendance</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="stat-card">
                  <i class="bi bi-check-circle-fill text-success"></i>
                  <h3><?= $attendanceStats['present'] ?></h3>
                  <p>Present</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="stat-card">
                  <i class="bi bi-clock-fill text-warning"></i>
                  <h3><?= $attendanceStats['late'] ?></h3>
                  <p>Late</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="stat-card">
                  <i class="bi bi-x-circle-fill text-danger"></i>
                  <h3><?= $attendanceStats['absent'] ?></h3>
                  <p>Absent</p>
                </div>
              </div>
            </div>
            <?php endif; ?>

            <!-- ✅ Meeting Details Section -->
            <?php if ($meetingDetails): ?>
            <div class="meeting-info">
              <h5><i class="bi bi-calendar-event me-2"></i><?= htmlspecialchars($meetingDetails['title']) ?></h5>
              <p><b>Subject:</b> <?= htmlspecialchars($meetingDetails['subject']) ?></p>
              <p><b>Owner:</b> <?= htmlspecialchars($meetingDetails['created_by']) ?> (<?= htmlspecialchars($meetingDetails['role']) ?>)</p>
              <p><b>Date:</b> <?= date("M d, Y", strtotime($meetingDetails['date'])) ?> 
                 | <b>Start Time:</b> <?= date("H:i", strtotime($meetingDetails['start_time'])) ?></p>
              <p><b>Zoom Link:</b> <a href="<?= htmlspecialchars($meetingDetails['zoom_link']) ?>" target="_blank" class="btn btn-sm btn-primary"><i class="bi bi-camera-video me-1"></i>Join Meeting</a></p>
            </div>
            <?php endif; ?>

            <!-- ✅ Attendance Table -->
            <?php if ($selectedMeeting): ?>
              <?php if ($attendance): ?>
                <div class="table-responsive">
                  <table id="attendanceTable" class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                      <tr>
                        <th>#</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Reg No</th>
                        <th>Log Time</th>
                        <th>Status</th>
                        <th>IP Address</th>
                        <th>User Agent</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($attendance as $row): ?>
                      <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['student_id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['regno']) ?></td>
                        <td><?= date("M d, Y H:i:s", strtotime($row['logtime'])) ?></td>
                        <td>
                          <?php if (strtolower($row['status'])=="present"): ?>
                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Present</span>
                          <?php elseif (strtolower($row['status'])=="late"): ?>
                            <span class="badge bg-warning"><i class="bi bi-clock me-1"></i>Late</span>
                          <?php else: ?>
                            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i><?= htmlspecialchars($row['status']) ?></span>
                          <?php endif; ?>
                        </td>
                        <td><?= $row['ip_address'] ?></td>
                        <td style="max-width:250px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" 
                            title="<?= htmlspecialchars($row['user_agent']) ?>">
                          <?= htmlspecialchars($row['user_agent']) ?>
                        </td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php else: ?>
                <div class="alert alert-warning mt-3">
                  <i class="bi bi-exclamation-triangle me-2"></i>No attendance records found for this meeting.
                </div>
              <?php endif; ?>
            <?php else: ?>
              <div class="text-center py-5">
                <i class="bi bi-calendar-check" style="font-size: 3rem; color: #ccc;"></i>
                <h4 class="mt-3" style="color: #888;">Select a meeting to view attendance</h4>
              </div>
            <?php endif; ?>

          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include_once("../includes/footer.php"); ?>
    <?php include_once("../includes/js-links-inc.php") ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
<script>
$(document).ready(function() {
    $('#attendanceTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                className: 'btn btn-sm btn-primary',
                text: '<i class="bi bi-clipboard me-1"></i>Copy'
            },
            {
                extend: 'excel',
                className: 'btn btn-sm btn-success',
                text: '<i class="bi bi-file-earmark-excel me-1"></i>Excel'
            },
            {
                extend: 'pdf',
                className: 'btn btn-sm btn-danger',
                text: '<i class="bi bi-file-earmark-pdf me-1"></i>PDF'
            },
            {
                extend: 'print',
                className: 'btn btn-sm btn-info',
                text: '<i class="bi bi-printer me-1"></i>Print'
            },
            {
                extend: 'colvis',
                className: 'btn btn-sm btn-secondary',
                text: '<i class="bi bi-eye me-1"></i>Columns'
            }
        ],
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search records..."
        }
    });
});
</script>
</body>
</html>
<?php $conn->close(); ?>
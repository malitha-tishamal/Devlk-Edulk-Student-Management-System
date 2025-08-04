<?php
session_start();
require_once '../includes/db-conn.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Zoom Meetings</title>
  <?php include_once("../includes/css-links-inc.php"); ?>
  <style>
    .resource-list ul { padding-left: 20px; }
    .resource-list li { margin-bottom: 6px; }
    .resource-list a { text-decoration: none; }
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
  </style>
</head>
<body>

<?php include_once("../includes/header.php"); ?>
<?php include_once("../includes/student-sidebar.php"); ?>

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Available Zoom Meetings</h1>
  </div>

  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Meetings List</h5>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Title</th>
            <th>Date</th>
            <th>Time</th>
            <th>Created By</th>
            <th>Role</th>
            <th>Meeting Status</th>
            <th>Link Expiry</th>
            <th>Subject</th>
            <th>Zoom Link</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $result = $conn->query("SELECT * FROM meetings WHERE status = 'active' ORDER BY date DESC");
        while ($row = $result->fetch_assoc()):
        ?>
          <tr id="meeting-<?= $row['id'] ?>">
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['date']) ?></td>
            <td><?= htmlspecialchars($row['start_time']) ?></td>
            <td><?= htmlspecialchars($row['created_by']) ?></td>
            <td><?= htmlspecialchars($row['role']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td><?= htmlspecialchars($row['link_expiry_status']) ?></td>
            <td><?= htmlspecialchars($row['subject']) ?></td>
            <td><a href="<?= htmlspecialchars($row['zoom_link']) ?>" target="_blank" class="btn btn-sm btn-primary">Join</a></td>
          </tr>
          <tr>
            <td colspan="9">
              <div class="row">
                <div class="col-md-6">
                  <div class="resource-list">
                  <?php
                    $stmt = $conn->prepare("SELECT * FROM meeting_resources WHERE meeting_id = ?");
                    $stmt->bind_param("i", $row['id']);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    if ($res->num_rows > 0):
                      echo "<ul>";
                      while ($resRow = $res->fetch_assoc()):
                        $style = $resRow['status'] === 'disabled' ? "style='opacity:0.5;'" : "";
                        echo "<li $style>";
                        if ($resRow['resource_type'] === 'file') {
                          echo "<a href='../uploads/meeting_resources/{$resRow['resource_data']}' target='_blank' download><i class='fa fa-file'></i> {$resRow['resource_data']}</a>";
                        } else {
                          echo "<a href='{$resRow['resource_data']}' target='_blank'><i class='fa fa-link'></i> {$resRow['resource_data']}</a>";
                        }
                        echo "</li>";
                      endwhile;
                      echo "</ul>";
                    else:
                      echo "<p>No resources available.</p>";
                    endif;
                    $stmt->close();
                  ?>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="chat-container">
                    <h6>Chat</h6>
                    <div class="chat-messages">
                    <?php
                      $stmt = $conn->prepare("SELECT * FROM meeting_chat WHERE meeting_id = ? ORDER BY created_at ASC");
                      $stmt->bind_param("i", $row['id']);
                      $stmt->execute();
                      $chatRes = $stmt->get_result();
                      while ($chat = $chatRes->fetch_assoc()):
                        $time = date("h:i A", strtotime($chat['created_at']));
                        echo "<div><strong>{$chat['user_name']}:</strong> {$chat['message']} <small class='text-muted'>[{$time}]</small></div>";
                      endwhile;
                      $stmt->close();
                    ?>
                    </div>
                    <form method="post" action="send_meeting_chat2.php" class="chat-form d-flex gap-2">
                      <input type="hidden" name="meeting_id" value="<?= $row['id'] ?>">
                      <input type="text" name="message" class="form-control" placeholder="Type a message..." required>
                      <button type="submit" class="btn btn-primary btn-sm">Send</button>
                    </form>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<?php include_once("../includes/footer2.php"); ?>
<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
<?php include_once("../includes/js-links-inc.php"); ?>

</body>
</html>
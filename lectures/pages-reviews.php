<?php
session_start();
require_once '../includes/db-conn.php';

// Redirect if not logged in
if (!isset($_SESSION['lecture_id'])) {
    header("Location: ../index.php");
    exit();
}

// Check if logged in
$isLoggedIn = isset($_SESSION['lecture_id']);
$user = null;

if ($isLoggedIn) {
    $user_id = $_SESSION['lecture_id'];
    $sql = "SELECT * FROM lectures WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $review = trim($_POST['review']);
    $anonymous = isset($_POST['anonymous']);

    if ($anonymous || !$isLoggedIn) {
        $name = "Anonymous";
        $lecture_id = null;
    } else {
        $name = $user['name'];
        $lecture_id = $user['id'];
    }

    // Handle Multiple Images
    $uploadedImages = [];
    if (!empty($_FILES['review_images']['name'][0])) {
        $targetDir = "uploads/reviews/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        foreach ($_FILES['review_images']['tmp_name'] as $key => $tmpName) {
            $fileName = time() . "_" . basename($_FILES['review_images']['name'][$key]);
            $targetFile = $targetDir . $fileName;

            $fileType = mime_content_type($tmpName);
            if (!in_array($fileType, ['image/jpeg','image/png','image/gif'])) continue;
            if ($_FILES['review_images']['size'][$key] > 2*1024*1024) continue;

            if (move_uploaded_file($tmpName, $targetFile)) {
                $uploadedImages[] = $fileName;
            }
        }
    }

    $imagesJSON = !empty($uploadedImages) ? json_encode($uploadedImages) : null;

    // Insert review
    if (!empty($review)) {
        $stmt = $conn->prepare("INSERT INTO lectures_reviews (lecture_id, name, review, images) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $lecture_id, $name, $review, $imagesJSON);
        $stmt->execute();
        $stmt->close();

        $_SESSION['message'] = "Review submitted successfully!";
    } else {
        $_SESSION['message'] = "Please write something before submitting.";
    }

    header("Location: pages-reviews.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Review / Report - Edulk</title>
  <?php include_once("../includes/css-links-inc.php"); ?>
  <style>
    /* Your previous styles here ... */
    /* Review card, gallery, modal, etc. */
  </style>
</head>
<body>
<?php include_once("../includes/header.php"); ?>
<?php include_once("../includes/lectures-sidebar.php"); ?>

<main id="main" class="main">
  <div class="pagetitle">
      <h1>User Review / Report</h1>
      <nav>
          <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Review</li>
          </ol>
      </nav>
  </div>

  <?php if (isset($_SESSION['message'])): ?>
      <div class="alert alert-info alert-dismissible fade show" role="alert">
          <?= $_SESSION['message']; unset($_SESSION['message']); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
  <?php endif; ?>

  <!-- Submit Review Section -->
  <section class="section">
    <div class="row">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header">
            <i class="bi bi-chat-dots"></i> Submit Your Review
          </div>
          <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
              <?php if ($isLoggedIn): ?>
                <div class="user-info">
                  <p><strong>Name:</strong> <?= htmlspecialchars($user['name']); ?></p>
                  <p><strong>Mobile No:</strong> <?= htmlspecialchars($user['mobile']); ?></p>
                  <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="anonymous" id="anonymous">
                    <label class="form-check-label" for="anonymous">
                      Submit as Anonymous
                    </label>
                  </div>
                </div>
              <?php else: ?>
                <div class="alert alert-info">
                  <i class="bi bi-info-circle"></i> You are not logged in. Your review will be posted as Anonymous.
                </div>
              <?php endif; ?>

              <div class="mb-3">
                <label for="review" class="form-label">Your Review / Report</label>
                <textarea class="form-control" id="review" name="review" rows="5" placeholder="Share your experience or report an issue..." required></textarea>
              </div>

              <div class="mb-4">
                <label for="reviewImages" class="form-label">Upload Images (optional)</label>
                <input type="file" class="form-control" id="reviewImages" name="review_images[]" accept="image/*" multiple>
                <div class="form-text">You can select multiple images. Maximum size per image: 2MB</div>
              </div>

              <button type="submit" class="btn btn-primary">
                <i class="bi bi-send"></i> Submit Review
              </button>
            </form>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card">
          <div class="card-header">
            <i class="bi bi-lightbulb"></i> Review Guidelines
          </div>
          <div class="card-body">
            <ul class="list-group list-group-flush">
              <li class="list-group-item">• Be respectful and constructive</li>
              <li class="list-group-item">• Focus on your personal experience</li>
              <li class="list-group-item">• Provide specific details when reporting issues</li>
              <li class="list-group-item">• Avoid sharing sensitive personal information</li>
              <li class="list-group-item">• Upload relevant images to support your review</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Recent Reviews Section -->
  <section class="section">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <i class="bi bi-chat-square-text"></i> Recent Reviews
          </div>
          <div class="card-body">
            <?php
            $result = $conn->query("SELECT * FROM lectures_reviews ORDER BY created_at DESC LIMIT 10");
            if ($result->num_rows > 0): 
                while ($row = $result->fetch_assoc()): ?>
                    <div class="review-card">
                      <div class="review-header">
                        <div class="reviewer-info">
                          <span class="reviewer-name"><?= htmlspecialchars($row['name']); ?></span>
                        </div>
                        <span class="review-date"><?= date('M j, Y g:i A', strtotime($row['created_at'])); ?></span>
                      </div>
                      <div class="review-content">
                        <p class="review-text"><?= nl2br(htmlspecialchars($row['review'])); ?></p>
                        
                        <?php 
                        if (!empty($row['images'])):
                            $images = json_decode($row['images']); ?>
                            <div class="image-gallery">
                              <?php foreach ($images as $img): ?>
                                <div class="gallery-image" data-bs-toggle="modal" data-bs-target="#imageModal" data-img-src="uploads/reviews/<?= htmlspecialchars($img); ?>">
                                  <img src="uploads/reviews/<?= htmlspecialchars($img); ?>" alt="Review Image">
                                </div>
                              <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Admin Reply + Read/Not Read -->
                        <div class="card mt-3 p-3" style="background-color: #f1f5f9; border-left: 4px solid #4361ee;">
                          <strong>Admin Reply:</strong>
                          <p>
                            <?= !empty($row['admin_reply']) ? nl2br(htmlspecialchars($row['admin_reply'])) : '<em>No reply yet</em>'; ?>
                          </p>
                          <span style="font-size: 13px; color: <?= (!empty($row['admin_reply']) && $row['admin_reply_read']) ? '#28a745' : '#ff0000'; ?>;">
                            <?= (!empty($row['admin_reply']) && $row['admin_reply_read']) ? 'Admin Read This Post' : 'Not Read yet'; ?>
                          </span>
                        </div>
                      </div>
                    </div>
                <?php endwhile; 
            else: ?>
                <div class="empty-state">
                  <i class="bi bi-chat-square"></i>
                  <p>No reviews yet. Be the first to share your experience!</p>
                </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Review Image</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img src="" class="modal-image" id="modalImage" alt="Review Image">
      </div>
    </div>
  </div>
</div>

<?php include_once("../includes/footer.php"); ?>
<?php include_once("../includes/js-links-inc.php"); ?>

<script>
  const imageModal = document.getElementById('imageModal');
  if (imageModal) {
    imageModal.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const imgSrc = button.getAttribute('data-img-src');
      const modalImage = imageModal.querySelector('#modalImage');
      modalImage.src = imgSrc;
    });
  }

  const reviewTextarea = document.getElementById('review');
  if (reviewTextarea) {
    const charCount = document.createElement('div');
    charCount.className = 'form-text text-end';
    charCount.id = 'charCount';
    charCount.textContent = '0 characters';
    reviewTextarea.parentNode.appendChild(charCount);
    
    reviewTextarea.addEventListener('input', function() {
      charCount.textContent = this.value.length + ' characters';
    });
  }
</script>
</body>
</html>

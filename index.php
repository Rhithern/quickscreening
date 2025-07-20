<?php
$pageTitle = "Home";
include 'includes/header.php';
?>

<div class="text-center">
  <h1 class="display-5 fw-bold">Welcome to <?= htmlspecialchars(SITE_NAME) ?></h1>
  <p class="lead mt-3">Upload, manage, and share your videos securely with our private portal.</p>
  <a href="/upload_video.php" class="btn btn-primary btn-lg mt-4">Get Started</a>
</div>

<?php include 'includes/footer.php'; ?>

<?php
// admin/view_interview.php
require_once '../includes/auth.php'; // admin auth check
require_once '../includes/db.php';

if (!isset($_GET['id'])) {
    die('Interview ID missing');
}
$id = intval($_GET['id']);

// Fetch interview video path
$stmt = $pdo->prepare("SELECT video_path FROM interviews WHERE id = ?");
$stmt->execute([$id]);
$interview = $stmt->fetch();

if (!$interview) {
    die('Interview not found');
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>View Interview</title>
</head>
<body>
  <h1>Interview Video</h1>
  <video width="640" height="480" controls>
    <source src="/uploads/<?= htmlspecialchars(basename($interview['video_path'])) ?>" type="video/webm">
    Your browser does not support the video tag.
  </video>
</body>
</html>

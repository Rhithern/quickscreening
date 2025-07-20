<?php
session_start();
require_once '../includes/auth.php';  // Checks admin login
require_once '../includes/db.php';

if (!isset($_GET['interview_id'])) {
    die('Interview ID is required.');
}

$interviewId = intval($_GET['interview_id']);

// Admins can view any interview, no candidate restriction
$stmt = $pdo->prepare("SELECT video_filename FROM interviews WHERE id = ?");
$stmt->execute([$interviewId]);
$interview = $stmt->fetch();

if (!$interview) {
    die('Interview not found.');
}

$videoFilename = $interview['video_filename'];

$pageTitle = 'View Interview Video';
include '../includes/header.php';
?>

<h1>Interview Video Playback (Admin)</h1>

<video controls width="720" preload="metadata">
  <source src="/video_stream.php?video=<?= htmlspecialchars($videoFilename) ?>" type="video/webm">
  Your browser does not support the video tag.
</video>

<?php include '../includes/footer.php'; ?>

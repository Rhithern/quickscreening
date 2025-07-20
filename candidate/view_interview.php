<?php
session_start();
require_once '../includes/auth.php'; // candidate auth check
require_once '../includes/db.php';

if (!isset($_GET['interview_id'])) {
    die('Interview ID required');
}

$interviewId = intval($_GET['interview_id']);
$userId = $_SESSION['user_id'];

// Verify this interview belongs to the logged-in candidate
$stmt = $pdo->prepare("SELECT video_filename FROM interviews WHERE id = ? AND candidate_id = ?");
$stmt->execute([$interviewId, $userId]);
$interview = $stmt->fetch();

if (!$interview) {
    die('Interview not found or access denied.');
}

$videoFilename = $interview['video_filename'];

$pageTitle = 'View Interview Video';
include '../includes/header.php';
?>

<h1>Interview Video Playback</h1>

<video controls width="720" preload="metadata">
  <source src="/video_stream.php?video=<?= htmlspecialchars($videoFilename) ?>" type="video/webm">
  Your browser does not support the video tag.
</video>

<?php include '../includes/footer.php'; ?>


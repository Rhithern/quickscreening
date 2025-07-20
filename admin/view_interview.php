<?php
require_once '../includes/auth.php';  // Admin authentication
require_once '../includes/db.php';

$pageTitle = 'View Interview Details';

$token = $_GET['token'] ?? '';
if (!$token) {
    header('Location: dashboard.php?error=No interview specified');
    exit;
}

// Handle POST actions (delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['token']) || $_POST['token'] !== $token) {
        die('Invalid token.');
    }

    if ($_POST['action'] === 'delete') {
        // Fetch video filename
        $stmt = $pdo->prepare("SELECT video_filename FROM interviews WHERE token = ?");
        $stmt->execute([$token]);
        $video = $stmt->fetchColumn();

        // Delete video file if exists
        if ($video) {
            $filePath = $_SERVER['DOCUMENT_ROOT'] . '/assets/uploads/' . $video;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Delete interview record
        $stmt = $pdo->prepare("DELETE FROM interviews WHERE token = ?");
        $stmt->execute([$token]);

        header('Location: dashboard.php?msg=Interview deleted successfully');
        exit;
    }
}

// Fetch interview data with position title
$stmt = $pdo->prepare("SELECT i.*, p.title AS position_title
                       FROM interviews i
                       JOIN positions p ON i.position_id = p.id
                       WHERE i.token = ? LIMIT 1");
$stmt->execute([$token]);
$interview = $stmt->fetch();

if (!$interview) {
    header('Location: dashboard.php?error=Interview not found');
    exit;
}

$videoPath = '/assets/uploads/' . $interview['video_filename'];
$videoFullPath = $_SERVER['DOCUMENT_ROOT'] . $videoPath;
$videoExists = $interview['video_filename'] && file_exists($videoFullPath);

include '../includes/header.php';
?>

<h2>Interview Details</h2>

<table class="table table-bordered w-75 mx-auto">
    <tr>
        <th>Candidate Email</th>
        <td><?= htmlspecialchars($interview['candidate_email']) ?></td>
    </tr>
    <tr>
        <th>Position</th>
        <td><?= htmlspecialchars($interview['position_title']) ?></td>
    </tr>
    <tr>
        <th>Status</th>
        <td><?= htmlspecialchars(ucfirst($interview['status'])) ?></td>
    </tr>
    <tr>
        <th>Interview Scheduled</th>
        <td><?= htmlspecialchars($interview['created_at']) ?></td>
    </tr>
    <tr>
        <th>Video Submission Time</th>
        <td><?= htmlspecialchars($interview['submitted_at'] ?? 'Not submitted yet') ?></td>
    </tr>
</table>

<?php if ($videoExists): ?>
  <div class="text-center mt-4">
    <h5>Candidate Video Response</h5>
    <video width="640" height="480" controls>
      <source src="<?= $videoPath ?>" type="video/webm">
      Your browser does not support the video tag.
    </video>
  </div>
<?php else: ?>
  <div class="alert alert-warning text-center">No video uploaded yet.</div>
<?php endif; ?>

<div class="mt-4 text-center">
  <form method="post" onsubmit="return confirm('Are you sure you want to delete this interview?');" class="d-inline">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
    <button type="submit" class="btn btn-danger">Delete Interview</button>
  </form>

  <a href="edit_interview.php?token=<?= urlencode($token) ?>" class="btn btn-primary ms-2">Edit Interview</a>
</div>

<a href="/admin/dashboard.php" class="btn btn-secondary mt-4 d-block mx-auto w-25">Back to Dashboard</a>

<?php include '../includes/footer.php'; ?>

<?php
require_once '../includes/auth.php';  // Admin must be logged in
require_once '../includes/db.php';
$pageTitle = 'View Interview Details';

include '../includes/header.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    echo '<div class="alert alert-danger">No interview specified.</div>';
    include '../includes/footer.php';
    exit;
}

// Fetch interview info
$stmt = $pdo->prepare("SELECT i.*, p.title AS position_title
                       FROM interviews i
                       JOIN positions p ON i.position_id = p.id
                       WHERE i.token = ? LIMIT 1");
$stmt->execute([$token]);
$interview = $stmt->fetch();

if (!$interview) {
    echo '<div class="alert alert-danger">Interview not found.</div>';
    include '../includes/footer.php';
    exit;
}

// Check if video file exists
$videoPath = '/assets/uploads/' . $interview['video_filename'];  // Make sure this matches your storage
$videoFullPath = $_SERVER['DOCUMENT_ROOT'] . $videoPath;
$videoExists = $interview['video_filename'] && file_exists($videoFullPath);

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

<a href="/admin/dashboard.php" class="btn btn-secondary mt-4">Back to Dashboard</a>

<?php include '../includes/footer.php'; ?>

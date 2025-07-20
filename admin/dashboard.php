<?php
session_start();
require_once '../includes/auth.php';    // Admin authentication check
require_once '../includes/db.php';

// Fetch counts for summary stats
$totalPositions = $pdo->query("SELECT COUNT(*) FROM positions")->fetchColumn();
$totalQuestions = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
$totalCandidates = $pdo->query("SELECT COUNT(*) FROM candidates")->fetchColumn();
$totalInterviews = $pdo->query("SELECT COUNT(*) FROM interviews")->fetchColumn();

// Fetch recent interviews (latest 10)
$stmt = $pdo->query("
    SELECT i.id, p.name AS position_name, c.email AS candidate_email, i.interview_date
    FROM interviews i
    JOIN positions p ON i.position_id = p.id
    JOIN candidates c ON i.candidate_id = c.id
    ORDER BY i.interview_date DESC
    LIMIT 10
");
$recentInterviews = $stmt->fetchAll();

$pageTitle = "Admin Dashboard";
include '../includes/header.php';
?>

<h1>Welcome, Admin!</h1>

<nav class="mb-4">
  <a href="list_positions.php" class="btn btn-outline-primary me-2">Positions</a>
  <a href="list_questions.php" class="btn btn-outline-primary me-2">Questions</a>
  <a href="create_position.php" class="btn btn-outline-success me-2">Create Position</a>
  <a href="add_questions.php" class="btn btn-outline-success me-2">Add Questions</a>
  <a href="send_invite.php" class="btn btn-outline-info me-2">Send Interview Invite</a>
  <a href="customize_brand.php" class="btn btn-outline-warning me-2">Customize Branding</a>
  <a href="export_reports.php" class="btn btn-outline-secondary me-2">Export Reports</a>
  <a href="register.php" class="btn btn-outline-dark me-2">Register Admin</a>
  <a href="logout.php" class="btn btn-danger float-end">Logout</a>
</nav>

<div class="row mb-5">
  <div class="col-md-3">
    <div class="card text-white bg-primary mb-3">
      <div class="card-body">
        <h5 class="card-title">Positions</h5>
        <p class="card-text fs-3"><?= $totalPositions ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-white bg-success mb-3">
      <div class="card-body">
        <h5 class="card-title">Questions</h5>
        <p class="card-text fs-3"><?= $totalQuestions ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-white bg-info mb-3">
      <div class="card-body">
        <h5 class="card-title">Candidates</h5>
        <p class="card-text fs-3"><?= $totalCandidates ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-white bg-warning mb-3">
      <div class="card-body">
        <h5 class="card-title">Interviews</h5>
        <p class="card-text fs-3"><?= $totalInterviews ?></p>
      </div>
    </div>
  </div>
</div>

<h2>Recent Interviews</h2>
<?php if ($recentInterviews): ?>
<table class="table table-striped">
  <thead>
    <tr>
      <th>Position</th>
      <th>Candidate Email</th>
      <th>Date</th>
      <th>Video</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($recentInterviews as $interview): ?>
      <tr>
        <td><?= htmlspecialchars($interview['position_name']) ?></td>
        <td><?= htmlspecialchars($interview['candidate_email']) ?></td>
        <td><?= htmlspecialchars($interview['interview_date']) ?></td>
        <td>
          <a href="view_interview.php?interview_id=<?= $interview['id'] ?>" class="btn btn-sm btn-primary">View Video</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
  <p>No interviews found.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>

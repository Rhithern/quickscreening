<?php
require_once '../includes/auth.php';  // Admin authentication
require_once '../includes/db.php';

$pageTitle = 'Edit Interview';

$token = $_GET['token'] ?? '';
if (!$token) {
    header('Location: dashboard.php?error=No interview specified');
    exit;
}

// Fetch interview to edit
$stmt = $pdo->prepare("SELECT * FROM interviews WHERE token = ? LIMIT 1");
$stmt->execute([$token]);
$interview = $stmt->fetch();

if (!$interview) {
    header('Location: dashboard.php?error=Interview not found');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $candidate_email = filter_var($_POST['candidate_email'], FILTER_VALIDATE_EMAIL);
    $status = $_POST['status'] ?? '';

    if (!$candidate_email) {
        $errors[] = 'Please enter a valid candidate email.';
    }
    $valid_statuses = ['pending', 'completed', 'cancelled'];
    if (!in_array($status, $valid_statuses)) {
        $errors[] = 'Invalid status selected.';
    }

    if (empty($errors)) {
        // Update DB
        $stmt = $pdo->prepare("UPDATE interviews SET candidate_email = ?, status = ? WHERE token = ?");
        $stmt->execute([$candidate_email, $status, $token]);

        $success = 'Interview updated successfully.';
        // Refresh interview data
        $interview['candidate_email'] = $candidate_email;
        $interview['status'] = $status;
    }
}

include '../includes/header.php';
?>

<h2>Edit Interview</h2>

<?php if ($errors): ?>
  <div class="alert alert-danger">
    <ul>
      <?php foreach($errors as $err): ?>
        <li><?= htmlspecialchars($err) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if ($success): ?>
  <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="post" class="w-50 mx-auto">
  <div class="mb-3">
    <label for="candidate_email" class="form-label">Candidate Email</label>
    <input type="email" id="candidate_email" name="candidate_email" class="form-control" required value="<?= htmlspecialchars($interview['candidate_email']) ?>">
  </div>

  <div class="mb-3">
    <label for="status" class="form-label">Status</label>
    <select id="status" name="status" class="form-select" required>
      <option value="pending" <?= $interview['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
      <option value="completed" <?= $interview['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
      <option value="cancelled" <?= $interview['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
    </select>
  </div>

  <button type="submit" class="btn btn-primary">Update Interview</button>
  <a href="view_interview.php?token=<?= urlencode($token) ?>" class="btn btn-secondary ms-2">Cancel</a>
</form>

<?php include '../includes/footer.php'; ?>

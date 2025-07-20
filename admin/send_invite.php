<?php
require_once '../includes/auth.php';  // Ensure admin is logged in
require_once '../includes/db.php';
require_once '../includes/email.php'; // Contains your mail sending functions
$pageTitle = 'Send Interview Invite';

include '../includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $candidateEmail = filter_var(trim($_POST['candidate_email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $positionId = intval($_POST['position_id'] ?? 0);

    if (!$candidateEmail) {
        $error = "Please enter a valid candidate email.";
    } elseif (!$positionId) {
        $error = "Please select a position.";
    } else {
        // Fetch position info
        $stmt = $pdo->prepare("SELECT * FROM positions WHERE id = ?");
        $stmt->execute([$positionId]);
        $position = $stmt->fetch();

        if (!$position) {
            $error = "Invalid position selected.";
        } else {
            // Generate unique interview token/link
            $token = bin2hex(random_bytes(16));
            $expires = date('Y-m-d H:i:s', strtotime('+7 days')); // Link valid for 7 days

            // Insert interview invite into interviews table
            $stmt = $pdo->prepare("INSERT INTO interviews (candidate_email, position_id, token, expires_at, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->execute([$candidateEmail, $positionId, $token, $expires]);

            $interviewUrl = "https://" . $_SERVER['HTTP_HOST'] . "/candidate/interview.php?token=$token";

            // Prepare email content
            $subject = "Interview Invitation for Position: " . htmlspecialchars($position['title']);
            $message = "Dear Candidate,\n\nYou have been invited to complete a one-way interview for the position: " . htmlspecialchars($position['title']) . ".\n\nPlease complete your interview by clicking the following link:\n\n$interviewUrl\n\nThis link is valid until " . $expires . ".\n\nBest regards,\n" . SITE_NAME;

            // Send email
            $mailSent = mail($candidateEmail, $subject, $message);

            if ($mailSent) {
                $success = "Interview invitation sent successfully to $candidateEmail.";
            } else {
                $error = "Failed to send email. Please check mail configuration.";
            }
        }
    }
}

// Fetch positions for dropdown
$positionsStmt = $pdo->query("SELECT id, title FROM positions ORDER BY title");
$positions = $positionsStmt->fetchAll();
?>

<h2>Send Interview Invitation</h2>

<?php if ($error): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($success): ?>
  <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="post" novalidate>
  <div class="mb-3">
    <label for="candidate_email" class="form-label">Candidate Email</label>
    <input type="email" class="form-control" id="candidate_email" name="candidate_email" required value="<?= htmlspecialchars($_POST['candidate_email'] ?? '') ?>">
  </div>

  <div class="mb-3">
    <label for="position_id" class="form-label">Select Position</label>
    <select class="form-select" id="position_id" name="position_id" required>
      <option value="">-- Select Position --</option>
      <?php foreach ($positions as $pos): ?>
        <option value="<?= $pos['id'] ?>" <?= (isset($_POST['position_id']) && $_POST['position_id'] == $pos['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($pos['title']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <button type="submit" class="btn btn-primary">Send Invitation</button>
</form>

<?php include '../includes/footer.php'; ?>


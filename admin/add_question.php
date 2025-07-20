<?php
require_once '../includes/auth_admin.php';
require_once '../includes/db_connect.php';
$pageTitle = 'Add Question';
include '../includes/header.php';

// Fetch positions for dropdown
$stmt = $pdo->query("SELECT id, title FROM positions ORDER BY title");
$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $position_id = $_POST['position_id'] ?? '';
    $question_text = trim($_POST['question_text'] ?? '');

    if (empty($position_id) || empty($question_text)) {
        $error = 'Please select a position and enter the question.';
    } else {
        // Insert question
        $stmt = $pdo->prepare("INSERT INTO questions (position_id, question_text) VALUES (?, ?)");
        if ($stmt->execute([$position_id, $question_text])) {
            header('Location: list_questions.php');
            exit;
        } else {
            $error = 'Failed to add question.';
        }
    }
}
?>

<h2>Add New Question</h2>

<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post">
  <div class="mb-3">
    <label for="position_id" class="form-label">Position</label>
    <select id="position_id" name="position_id" class="form-select" required>
      <option value="">Select Position</option>
      <?php foreach ($positions as $pos): ?>
        <option value="<?= $pos['id'] ?>" <?= (isset($position_id) && $position_id == $pos['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($pos['title']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="mb-3">
    <label for="question_text" class="form-label">Question</label>
    <textarea id="question_text" name="question_text" class="form-control" rows="3" required><?= htmlspecialchars($question_text ?? '') ?></textarea>
  </div>
  <button type="submit" class="btn btn-primary">Add Question</button>
  <a href="list_questions.php" class="btn btn-secondary">Cancel</a>
</form>

<?php include '../includes/footer.php'; ?>

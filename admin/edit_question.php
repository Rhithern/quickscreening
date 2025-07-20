<?php
require_once '../includes/auth_admin.php';
require_once '../includes/db_connect.php';
$pageTitle = 'Edit Question';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: list_questions.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
$stmt->execute([$id]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$question) {
    header('Location: list_questions.php');
    exit;
}

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
        $stmt = $pdo->prepare("UPDATE questions SET position_id = ?, question_text = ? WHERE id = ?");
        if ($stmt->execute([$position_id, $question_text, $id])) {
            header('Location: list_questions.php');
            exit;
        } else {
            $error = 'Failed to update question.';
        }
    }
} else {
    // Set defaults for form fields
    $position_id = $question['position_id'];
    $question_text = $question['question_text'];
}
?>

<h2>Edit Question</h2>

<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post">
  <div class="mb-3">
    <label for="position_id" class="form-label">Position</label>
    <select id="position_id" name="position_id" class="form-select" required>
      <option value="">Select Position</option>
      <?php foreach ($positions as $pos): ?>
        <option value="<?= $pos['id'] ?>" <?= ($position_id == $pos['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($pos['title']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="mb-3">
    <label for="question_text" class="form-label">Question</label>
    <textarea id="question_text" name="question_text" class="form-control" rows="3" required><?= htmlspecialchars($question_text) ?></textarea>
  </div>
  <button type="submit" class="btn btn-primary">Save Changes</button>
  <a href="list_questions.php" class="btn btn-secondary">Cancel</a>
</form>

<?php include '../includes/footer.php'; ?>

<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $position_id = $_POST['position_id'] ?? '';
    $question = $_POST['question'] ?? '';
    $type = $_POST['type'] ?? 'text';

    if ($position_id && $question) {
        $stmt = $pdo->prepare("INSERT INTO questions (position_id, question, type) VALUES (?, ?, ?)");
        $stmt->execute([$position_id, $question, $type]);
        $success = "Question added successfully!";
    } else {
        $error = "Please fill in all required fields.";
    }
}

// Get positions for dropdown
$positions = $pdo->query("SELECT id, title FROM positions ORDER BY created_at DESC")->fetchAll();
?>

<div class="container py-5">
    <h2 class="mb-4">Add Interview Question</h2>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label for="position_id" class="form-label">Select Position</label>
            <select name="position_id" id="position_id" class="form-select" required>
                <option value="">-- Choose Position --</option>
                <?php foreach ($positions as $position): ?>
                    <option value="<?= $position['id'] ?>"><?= htmlspecialchars($position['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="question" class="form-label">Question</label>
            <textarea name="question" id="question" rows="4" class="form-control" required></textarea>
        </div>

        <div class="mb-3">
            <label for="type" class="form-label">Answer Type</label>
            <select name="type" id="type" class="form-select">
                <option value="text">Text</option>
                <option value="video">Video</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Add Question</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>


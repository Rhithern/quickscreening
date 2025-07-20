<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $deadline = $_POST['deadline'] ?? null;

    if ($title && $description) {
        $stmt = $pdo->prepare("INSERT INTO positions (title, description, deadline) VALUES (?, ?, ?)");
        $stmt->execute([$title, $description, $deadline]);
        $success = "Position created successfully!";
    } else {
        $error = "Please provide both a title and description.";
    }
}
?>

<div class="container py-5">
    <h2 class="mb-4">Create New Job Position</h2>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label for="title" class="form-label">Position Title</label>
            <input type="text" name="title" id="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Position Description</label>
            <textarea name="description" id="description" rows="5" class="form-control" required></textarea>
        </div>

        <div class="mb-3">
            <label for="deadline" class="form-label">Application Deadline (optional)</label>
            <input type="date" name="deadline" id="deadline" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Create Position</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>


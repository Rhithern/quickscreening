<?php
require_once '../includes/auth.php';  // Admin authentication
require_once '../includes/db.php';

$pageTitle = 'Edit Position';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: list_positions.php?error=Invalid position ID');
    exit;
}

// Fetch existing position details
$stmt = $pdo->prepare("SELECT * FROM positions WHERE id = ?");
$stmt->execute([$id]);
$position = $stmt->fetch();

if (!$position) {
    header('Location: list_positions.php?error=Position not found');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($title)) {
        $errors[] = 'Position title cannot be empty.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE positions SET title = ?, description = ? WHERE id = ?");
        $stmt->execute([$title, $description, $id]);

        $success = 'Position updated successfully.';
        // Refresh position data
        $position['title'] = $title;
        $position['description'] = $description;
    }
}

include '../includes/header.php';
?>

<h2>Edit Position</h2>

<?php if ($errors): ?>
  <div class="alert alert-danger">
    <ul>
      <?php foreach ($errors as $err): ?>
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
    <label for="title" class="form-label">Position Title</label>
    <input type="text" id="title" name="title" class="form-control" required value="<?= htmlspecialchars($position['title']) ?>">
  </div>

  <div class="mb-3">
    <label for="description" class="form-label">Position Description</label>
    <textarea id="description" name="description" class="form-control" rows="5"><?= htmlspecialchars($position['description']) ?></textarea>
  </div>

  <button type="submit" class="btn btn-primary">Update Position</button>
  <a href="list_positions.php" class="btn btn-secondary ms-2">Cancel</a>
</form>

<?php include '../includes/footer.php'; ?>

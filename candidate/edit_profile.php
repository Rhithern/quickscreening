<?php
session_start();
require_once '../includes/auth_candidate.php';
require_once '../includes/db.php';

$pageTitle = "Edit Profile";

$candidateId = $_SESSION['candidate_id'] ?? null;
if (!$candidateId) {
    header('Location: /login.php');
    exit;
}

// Fetch candidate data
$stmt = $pdo->prepare("SELECT name, email FROM candidates WHERE id = ?");
$stmt->execute([$candidateId]);
$candidate = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$candidate) {
    die('Candidate not found.');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name === '') $errors[] = 'Name is required.';
    if ($email === '') $errors[] = 'Email is required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';

    if ($password !== '') {
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirm_password) $errors[] = 'Passwords do not match.';
    }

    // Check if email is used by others
    $stmt = $pdo->prepare("SELECT id FROM candidates WHERE email = ? AND id != ?");
    $stmt->execute([$email, $candidateId]);
    if ($stmt->fetch()) {
        $errors[] = 'Email already in use by another account.';
    }

    if (empty($errors)) {
        if ($password !== '') {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE candidates SET name = ?, email = ?, password = ? WHERE id = ?";
            $stmt = $pdo->prepare($update_sql);
            $stmt->execute([$name, $email, $hashed_password, $candidateId]);
        } else {
            $update_sql = "UPDATE candidates SET name = ?, email = ? WHERE id = ?";
            $stmt = $pdo->prepare($update_sql);
            $stmt->execute([$name, $email, $candidateId]);
        }
        $success = "Profile updated successfully.";
    }
}

include '../includes/header.php';
?>

<h2>Edit Profile</h2>

<?php if ($success): ?>
  <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul>
      <?php foreach ($errors as $err): ?>
        <li><?= htmlspecialchars($err) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" novalidate>
  <div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input id="name" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? $candidate['name']) ?>" required>
  </div>
  <div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input id="email" name="email" type="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? $candidate['email']) ?>" required>
  </div>
  <div class="mb-3">
    <label for="password" class="form-label">New Password (leave blank to keep current)</label>
    <input id="password" name="password" type="password" class="form-control" autocomplete="new-password">
  </div>
  <div class="mb-3">
    <label for="confirm_password" class="form-label">Confirm New Password</label>
    <input id="confirm_password" name="confirm_password" type="password" class="form-control" autocomplete="new-password">
  </div>
  <button type="submit" class="btn btn-primary">Update Profile</button>
  <a href="dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
</form>

<?php include '../includes/footer.php'; ?>

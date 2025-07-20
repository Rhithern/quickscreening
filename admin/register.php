<?php
require_once '../includes/db.php';
require_once '../includes/auth.php'; // You might want to check if already logged in and redirect
$pageTitle = 'Admin Registration';

include '../includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $org_name = trim($_POST['org_name'] ?? '');

    if (!$email) {
        $error = 'Please enter a valid email.';
    } elseif (empty($password) || strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (empty($org_name)) {
        $error = 'Organization name is required.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare('SELECT id FROM admins WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email is already registered.';
        } else {
            // Insert new admin
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO admins (email, password, org_name) VALUES (?, ?, ?)');
            if ($stmt->execute([$email, $password_hash, $org_name])) {
                $success = 'Registration successful! You can now <a href="/admin/login.php">login</a>.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<h2>Admin Registration</h2>

<?php if ($error): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($success): ?>
  <div class="alert alert-success"><?= $success ?></div>
<?php else: ?>
<form method="post" novalidate>
  <div class="mb-3">
    <label for="email" class="form-label">Email address</label>
    <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
  </div>
  <div class="mb-3">
    <label for="org_name" class="form-label">Organization Name</label>
    <input type="text" class="form-control" id="org_name" name="org_name" required value="<?= htmlspecialchars($_POST['org_name'] ?? '') ?>">
  </div>
  <div class="mb-3">
    <label for="password" class="form-label">Password (min 6 characters)</label>
    <input type="password" class="form-control" id="password" name="password" required minlength="6">
  </div>
  <div class="mb-3">
    <label for="confirm_password" class="form-label">Confirm Password</label>
    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
  </div>
  <button type="submit" class="btn btn-primary">Register</button>
  <a href="/admin/login.php" class="btn btn-link">Back to Login</a>
</form>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>

<?php
session_start();
require_once '../includes/db.php'; // PDO connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $errors = [];

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Validate password length
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }

    // Check passwords match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM candidates WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "Email already registered.";
    }

    // If no errors, insert user
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO candidates (email, password) VALUES (?, ?)");
        if ($stmt->execute([$email, $hash])) {
            $_SESSION['success'] = "Registration successful! You can now login.";
            header("Location: ../login.php");
            exit;
        } else {
            $errors[] = "Database error. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Candidate Registration - QuickScreening</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-5">
<div class="container col-md-6 offset-md-3">
    <h2>Candidate Registration</h2>

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
            <label for="email" class="form-label">Email address</label>
            <input type="email" id="email" name="email" required class="form-control" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password (min 8 characters)</label>
            <input type="password" id="password" name="password" required class="form-control">
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
        <a href="../login.php" class="btn btn-link">Back to Login</a>
    </form>
</div>
</body>
</html>

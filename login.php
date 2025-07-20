<?php
session_start();
require_once 'includes/db.php'; // PDO connection
require_once 'includes/functions.php'; // Optional helper functions

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $user_type = $_POST['user_type'] ?? 'candidate'; // candidate or admin

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (empty($password)) {
        $error = 'Please enter your password.';
    } else {
        // Choose table based on user type
        $table = ($user_type === 'admin') ? 'admins' : 'candidates';

        $stmt = $pdo->prepare("SELECT * FROM $table WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Successful login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = $user_type;

            // Redirect to dashboard based on user type
            if ($user_type === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: candidate/dashboard.php');
            }
            exit;
        } else {
            $error = 'Invalid credentials.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Login - QuickScreening</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="p-5">
<div class="container col-md-4 offset-md-4">
    <h2 class="mb-4 text-center">Login to QuickScreening</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input
                type="email"
                id="email"
                name="email"
                required
                class="form-control"
                value="<?= isset($email) ? htmlspecialchars($email) : '' ?>"
            />
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" required class="form-control" />
        </div>

        <div class="mb-3">
            <label class="form-label">Login as:</label><br />
            <div class="form-check form-check-inline">
                <input
                    class="form-check-input"
                    type="radio"
                    name="user_type"
                    id="candidate"
                    value="candidate"
                    <?= (!isset($user_type) || $user_type === 'candidate') ? 'checked' : '' ?>
                />
                <label class="form-check-label" for="candidate">Candidate</label>
            </div>
            <div class="form-check form-check-inline">
                <input
                    class="form-check-input"
                    type="radio"
                    name="user_type"
                    id="admin"
                    value="admin"
                    <?= (isset($user_type) && $user_type === 'admin') ? 'checked' : '' ?>
                />
                <label class="form-check-label" for="admin">Admin</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <hr />
    <p class="text-center">New candidate? <a href="candidate/register.php">Sign up here</a></p>
    <p class="text-center">New admin? <a href="admin/register.php">Sign up here</a></p>
</div>
</body>
</html>

<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $table = ($role === 'admin') ? 'admins' : 'candidates';
    $sql = "SELECT * FROM $table WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $role;
        header("Location: /$role/dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light p-5">
    <div class="container col-md-4">
        <h2 class="mb-4">Login</h2>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST">
            <div class="mb-3">
                <label>Email:</label>
                <input name="email" type="email" required class="form-control">
            </div>
            <div class="mb-3">
                <label>Password:</label>
                <input name="password" type="password" required class="form-control">
            </div>
            <div class="mb-3">
                <label>Role:</label>
                <select name="role" class="form-select">
                    <option value="admin">Admin</option>
                    <option value="candidate">Candidate</option>
                </select>
            </div>
            <button class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</body>
</html>


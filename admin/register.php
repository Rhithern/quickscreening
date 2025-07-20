<?php
require_once '../includes/db.php'; // DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM candidates WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already registered";
        } else {
            // Insert new candidate
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO candidates (email, password) VALUES (?, ?)");
            $stmt->execute([$email, $hash]);

            header('Location: login.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Candidate Registration</title></head>
<body>
    <h2>Sign Up as Candidate</h2>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post">
        Email: <input type="email" name="email" required><br><br>
        Password: <input type="password" name="password" required><br><br>
        <button type="submit">Register</button>
    </form>
</body>
</html>


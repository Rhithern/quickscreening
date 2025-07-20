<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
</head>
<body class="p-4">
    <h2>Welcome, Admin</h2>
    <a href="create_position.php" class="btn btn-primary">Create Job Position</a>
    <a href="add_questions.php" class="btn btn-secondary">Add Questions</a>
    <a href="send_invite.php" class="btn btn-success">Send Interview Invite</a>
    <a href="customize_brand.php" class="btn btn-warning">Brand Settings</a>
    <a href="logout.php" class="btn btn-danger">Logout</a>
</body>
</html>


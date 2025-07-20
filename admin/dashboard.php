<?php
require_once '../includes/auth.php';  // restrict to logged-in admins
require_once '../includes/header.php';
?>

<h1>Admin Dashboard</h1>
<p>Welcome, <?= htmlspecialchars($_SESSION['user_email']) ?></p>

<?php require_once '../includes/footer.php'; ?>

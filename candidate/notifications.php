<?php
session_start();
require_once '../includes/auth_candidate.php';
require_once '../includes/db.php';

$candidateId = $_SESSION['candidate_id'];
$pageTitle = "Notifications";

include '../includes/header.php';

$notifications = getCandidateNotifications($pdo, $candidateId, 100, false);

// Mark notifications as read (simple approach)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read_id'])) {
    markNotificationRead($pdo, $_POST['mark_read_id'], $candidateId);
    header("Location: notifications.php");
    exit;
}
?>

<h2>Notifications</h2>

<?php if (empty($notifications)): ?>
    <div class="alert alert-info">No notifications.</div>
<?php else: ?>
    <ul class="list-group">
    <?php foreach ($notifications as $notif): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center <?= $notif['is_read'] ? '' : 'list-group-item-warning' ?>">
            <?= htmlspecialchars($notif['message']) ?>
            <form method="post" class="mb-0">
                <?php if (!$notif['is_read']): ?>
                    <input type="hidden" name="mark_read_id" value="<?= $notif['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-success" title="Mark as read">✓</button>
                <?php else: ?>
                    <span class="badge bg-secondary">Read</span>
                <?php endif; ?>
            </form>
        </li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>

<a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>

<?php include '../includes/footer.php'; ?>

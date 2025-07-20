<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Validate and fetch application
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid application ID.");
}

$app_id = $_GET['id'];

// Fetch application with position
$stmt = $pdo->prepare("
    SELECT a.*, p.title AS position_title 
    FROM applications a
    JOIN positions p ON a.position_id = p.id
    WHERE a.id = ?
");
$stmt->execute([$app_id]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$app) {
    die("Application not found.");
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $newStatus = $_POST['status'];
    $allowed = ['Pending', 'Accepted', 'Rejected'];

    if (in_array($newStatus, $allowed)) {
        $update = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
        $update->execute([$newStatus, $app_id]);
        $app['status'] = $newStatus;
        $message = "Status updated to <strong>$newStatus</strong>.";
    }
}
?>

<div class="container py-5">
    <h2 class="mb-4">View Application</h2>

    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">Candidate Info</div>
        <div class="card-body">
            <p><strong>Name:</strong> <?= htmlspecialchars($app['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($app['email']) ?></p>
            <p><strong>Position:</strong> <?= htmlspecialchars($app['position_title']) ?></p>
            <p><strong>Submitted At:</strong> <?= htmlspecialchars($app['created_at']) ?></p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Application Details</div>
        <div class="card-body">
            <?php if (!empty($app['resume_link'])): ?>
                <p><strong>Resume:</strong> <a href="<?= htmlspecialchars($app['resume_link']) ?>" target="_blank">View Resume</a></p>
            <?php endif; ?>
            <?php if (!empty($app['video_link'])): ?>
                <p><strong>Video Intro:</strong> <a href="<?= htmlspecialchars($app['video_link']) ?>" target="_blank">Watch Video</a></p>
            <?php endif; ?>

            <?php if (!empty($app['answers'])): ?>
                <p><strong>Answers:</strong></p>
                <pre class="bg-light p-3"><?= htmlspecialchars($app['answers']) ?></pre>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Update Status</div>
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <select name="status" class="form-select" required>
                        <option value="Pending" <?= $app['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Accepted" <?= $app['status'] === 'Accepted' ? 'selected' : '' ?>>Accepted</option>
                        <option value="Rejected" <?= $app['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update Status</button>
                <a href="view_applications.php" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

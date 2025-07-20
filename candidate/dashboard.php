<?php
require_once '../includes/auth.php';  // make sure it checks candidate login
require_once '../includes/db.php';

$pageTitle = 'Candidate Dashboard';
include '../includes/header.php';

// Get candidate id from session
$candidateId = $_SESSION['candidate_id'] ?? null;
if (!$candidateId) {
    header('Location: /login.php');
    exit;
}

// Fetch candidate details (optional)
$stmt = $pdo->prepare("SELECT * FROM candidates WHERE id = ?");
$stmt->execute([$candidateId]);
$candidate = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$candidate) {
    echo '<div class="alert alert-danger">Candidate not found.</div>';
    include '../includes/footer.php';
    exit;
}

// Fetch interviews for this candidate with position info and status
$sql = "SELECT i.*, p.title AS position_title
        FROM interviews i
        JOIN positions p ON i.position_id = p.id
        WHERE i.candidate_id = ?
        ORDER BY i.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$candidateId]);
$interviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Welcome, <?= htmlspecialchars($candidate['name'] ?? $candidate['email']) ?></h2>
<p>Your Interviews:</p>

<?php if (empty($interviews)): ?>
    <div class="alert alert-info">You currently have no scheduled interviews.</div>
<?php else: ?>
<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th>Position</th>
            <th>Scheduled On</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($interviews as $interview): ?>
            <tr>
                <td><?= htmlspecialchars($interview['position_title']) ?></td>
                <td><?= date('Y-m-d H:i', strtotime($interview['scheduled_at'] ?? $interview['created_at'])) ?></td>
                <td>
                    <?php
                        // You can expand this status logic as needed
                        switch ($interview['status']) {
                            case 'completed': echo '<span class="badge bg-success">Completed</span>'; break;
                            case 'pending': echo '<span class="badge bg-warning text-dark">Pending</span>'; break;
                            case 'cancelled': echo '<span class="badge bg-danger">Cancelled</span>'; break;
                            default: echo '<span class="badge bg-secondary">Unknown</span>'; break;
                        }
                    ?>
                </td>
                <td>
                    <?php if ($interview['status'] === 'pending'): ?>
                        <a href="interview.php?id=<?= $interview['id'] ?>" class="btn btn-sm btn-primary">Take Interview</a>
                    <?php else: ?>
                        <a href="view_interview.php?id=<?= $interview['id'] ?>" class="btn btn-sm btn-secondary">View Submission</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<a href="logout.php" class="btn btn-outline-danger mt-4">Logout</a>

<?php include '../includes/footer.php'; ?>


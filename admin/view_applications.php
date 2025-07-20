<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Fetch applications with position info
$stmt = $pdo->query("
    SELECT a.*, p.title AS position_title 
    FROM applications a
    JOIN positions p ON a.position_id = p.id
    ORDER BY a.created_at DESC
");
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-5">
    <h2 class="mb-4">Candidate Applications</h2>

    <?php if (count($applications) === 0): ?>
        <div class="alert alert-info">No applications submitted yet.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Candidate Name</th>
                        <th>Email</th>
                        <th>Position</th>
                        <th>Submitted At</th>
                        <th>Status</th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $index => $app): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($app['name']) ?></td>
                            <td><?= htmlspecialchars($app['email']) ?></td>
                            <td><?= htmlspecialchars($app['position_title']) ?></td>
                            <td><?= htmlspecialchars($app['created_at']) ?></td>
                            <td>
                                <?php
                                $status = $app['status'] ?? 'Pending';
                                $badge = match($status) {
                                    'Accepted' => 'success',
                                    'Rejected' => 'danger',
                                    default => 'secondary',
                                };
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= $status ?></span>
                            </td>
                            <td>
                                <a href="view_application.php?id=<?= $app['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    Review
                                </a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>

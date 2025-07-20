<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth_candidate.php';

$candidate_id = $_SESSION['candidate_id'];

// Fetch interview questions and answers
$stmt = $pdo->prepare("
    SELECT iq.id AS interview_question_id, q.question_text, a.video_path, a.submitted_at
    FROM interview_questions iq
    JOIN questions q ON iq.question_id = q.id
    LEFT JOIN answers a ON iq.id = a.question_id AND a.candidate_id = ?
    JOIN interviews i ON iq.interview_id = i.id
    WHERE i.candidate_id = ?
    ORDER BY iq.id ASC
");
$stmt->execute([$candidate_id, $candidate_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "My Interview Answers";
include '../includes/header.php';
?>

<h2>My Interview Answers</h2>

<?php if (!$results): ?>
    <p>You have no interview questions or answers yet.</p>
<?php else: ?>
    <?php foreach ($results as $row): ?>
        <div class="mb-4">
            <h5><?= htmlspecialchars($row['question_text']) ?></h5>
            <?php if ($row['video_path']): ?>
                <div class="ratio ratio-16x9 mb-2">
                    <video controls>
                        <source src="/uploads/answers/<?= htmlspecialchars($row['video_path']) ?>" type="video/webm">
                        <source src="/uploads/answers/<?= htmlspecialchars($row['video_path']) ?>" type="video/mp4">
                        <source src="/uploads/answers/<?= htmlspecialchars($row['video_path']) ?>" type="audio/ogg">
                        <source src="/uploads/answers/<?= htmlspecialchars($row['video_path']) ?>" type="audio/mpeg">
                        Your browser does not support the video or audio element.
                    </video>
                </div>
                <p class="text-muted small">Submitted at: <?= date('Y-m-d H:i', strtotime($row['submitted_at'])) ?></p>
            <?php else: ?>
                <p class="text-warning">No answer submitted yet.</p>
            <?php endif; ?>
        </div>
        <hr>
    <?php endforeach; ?>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>

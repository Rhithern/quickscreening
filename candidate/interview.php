<?php
require_once '../includes/auth.php';  // candidate auth check
require_once '../includes/db.php';

$pageTitle = 'Interview';
include '../includes/header.php';

$candidateId = $_SESSION['candidate_id'] ?? null;
if (!$candidateId) {
    header('Location: /login.php');
    exit;
}

// Get interview ID from URL
$interviewId = $_GET['id'] ?? null;
if (!$interviewId) {
    echo '<div class="alert alert-danger">Interview ID missing.</div>';
    include '../includes/footer.php';
    exit;
}

// Fetch interview and verify candidate ownership
$sql = "SELECT i.*, p.title AS position_title 
        FROM interviews i 
        JOIN positions p ON i.position_id = p.id 
        WHERE i.id = ? AND i.candidate_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$interviewId, $candidateId]);
$interview = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$interview) {
    echo '<div class="alert alert-danger">Interview not found or access denied.</div>';
    include '../includes/footer.php';
    exit;
}

// Fetch questions for this position
$sql = "SELECT * FROM questions WHERE position_id = ? ORDER BY id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$interview['position_id']]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Process submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic validation and save each answer
    foreach ($questions as $q) {
        $answerText = trim($_POST['answer'][$q['id']] ?? '');
        // Save or update the answer in DB, assuming table `answers` with columns: interview_id, question_id, answer_text
        $checkSql = "SELECT id FROM answers WHERE interview_id = ? AND question_id = ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$interviewId, $q['id']]);
        if ($checkStmt->rowCount()) {
            // Update
            $answerId = $checkStmt->fetchColumn();
            $updateSql = "UPDATE answers SET answer_text = ? WHERE id = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([$answerText, $answerId]);
        } else {
            // Insert
            $insertSql = "INSERT INTO answers (interview_id, question_id, answer_text) VALUES (?, ?, ?)";
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->execute([$interviewId, $q['id'], $answerText]);
        }
    }
    
    // Update interview status to completed
    $updateInterview = $pdo->prepare("UPDATE interviews SET status = 'completed', completed_at = NOW() WHERE id = ?");
    $updateInterview->execute([$interviewId]);
    
    // Send notification email to admin and candidate (optional)
    require_once '../includes/email.php';
    sendInterviewCompletionNotification(
        $interview['candidate_email'] ?? $_SESSION['candidate_email'], 
        $interview['position_title'], 
        ADMIN_EMAIL // Define ADMIN_EMAIL constant in config.php
    );
    
    echo '<div class="alert alert-success">Thank you for completing the interview.</div>';
    echo '<a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>';
    include '../includes/footer.php';
    exit;
}
?>

<h2>Interview for: <?= htmlspecialchars($interview['position_title']) ?></h2>

<form method="post" action="">
    <?php foreach ($questions as $index => $question): ?>
        <div class="mb-4">
            <label class="form-label"><strong>Question <?= $index + 1 ?>:</strong> <?= htmlspecialchars($question['question_text']) ?></label>
            <textarea class="form-control" name="answer[<?= $question['id'] ?>]" rows="4" required><?= htmlspecialchars($question['answer_text'] ?? '') ?></textarea>
        </div>
    <?php endforeach; ?>

    <button type="submit" class="btn btn-success">Submit Interview</button>
</form>

<?php include '../includes/footer.php'; ?>


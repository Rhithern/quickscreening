<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['candidate_id'])) {
    header('Location: login.php');
    exit;
}

$candidate_id = $_SESSION['candidate_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
        die('Upload failed. Please try again.');
    }

    $allowed_types = ['video/mp4', 'video/webm', 'audio/ogg', 'audio/mp3'];
    $mime_type = mime_content_type($_FILES['video']['tmp_name']);

    if (!in_array($mime_type, $allowed_types)) {
        die('Unsupported file type. Allowed: .mp4, .webm, .ogg, .mp3');
    }

    $ext = pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
    $upload_dir = '../uploads/answers/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $filename = uniqid('answer_') . '.' . $ext;
    $filepath = $upload_dir . $filename;
    move_uploaded_file($_FILES['video']['tmp_name'], $filepath);

    // Auto-detect current interview and question (latest unanswered)
    $stmt = $pdo->prepare("
        SELECT iq.id AS interview_question_id, iq.interview_id
        FROM interview_questions iq
        JOIN interviews i ON iq.interview_id = i.id
        WHERE i.candidate_id = ? AND iq.id NOT IN (
            SELECT question_id FROM answers WHERE candidate_id = ?
        )
        ORDER BY iq.id ASC LIMIT 1
    ");
    $stmt->execute([$candidate_id, $candidate_id]);
    $row = $stmt->fetch();

    if (!$row) {
        die('No pending questions found.');
    }

    $question_id = $row['interview_question_id'];
    $interview_id = $row['interview_id'];

    // Check if answer already exists
    $stmt = $pdo->prepare("SELECT id, video_path FROM answers WHERE candidate_id = ? AND question_id = ?");
    $stmt->execute([$candidate_id, $question_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Delete old video file
        $old_path = $upload_dir . $existing['video_path'];
        if (file_exists($old_path)) {
            unlink($old_path);
        }

        // Update existing answer
        $stmt = $pdo->prepare("UPDATE answers SET video_path = ?, submitted_at = NOW() WHERE id = ?");
        $stmt->execute([$filename, $existing['id']]);
    } else {
        // Insert new answer
        $stmt = $pdo->prepare("INSERT INTO answers (candidate_id, interview_id, question_id, video_path, submitted_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$candidate_id, $interview_id, $question_id, $filename]);
    }

    echo "Upload successful. Answer recorded.";
    echo "<br><a href='dashboard.php'>Back to Dashboard</a>";
} else {
    echo "Invalid request.";
}
?>

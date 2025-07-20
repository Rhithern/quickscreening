<?php
require_once '../includes/auth.php'; // Candidate auth
require_once '../includes/db.php';

header('Content-Type: application/json');

// Basic validations
if (!isset($_SESSION['candidate_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$interviewId = $_POST['interview_id'] ?? null;
$questionId = $_POST['question_id'] ?? null;

if (!$interviewId || !$questionId) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

if (!isset($_FILES['video'])) {
    echo json_encode(['success' => false, 'error' => 'No video uploaded']);
    exit;
}

// Validate file type & size (e.g., max 50MB)
$allowedTypes = ['video/webm', 'video/mp4', 'video/ogg'];
$fileType = $_FILES['video']['type'];

if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['success' => false, 'error' => 'Invalid video format']);
    exit;
}

if ($_FILES['video']['size'] > 50 * 1024 * 1024) {
    echo json_encode(['success' => false, 'error' => 'File too large']);
    exit;
}

// Save file securely
$uploadDir = __DIR__ . '/../uploads/answers/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$filename = uniqid('answer_', true) . '.' . pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
$targetPath = $uploadDir . $filename;

if (!move_uploaded_file($_FILES['video']['tmp_name'], $targetPath)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save file']);
    exit;
}

// Save video answer path to DB (answers table)
$sql = "SELECT id FROM answers WHERE interview_id = ? AND question_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$interviewId, $questionId]);
if ($stmt->rowCount()) {
    $answerId = $stmt->fetchColumn();
    $update = $pdo->prepare("UPDATE answers SET answer_video = ? WHERE id = ?");
    $update->execute([$filename, $answerId]);
} else {
    $insert = $pdo->prepare("INSERT INTO answers (interview_id, question_id, answer_video) VALUES (?, ?, ?)");
    $insert->execute([$interviewId, $questionId, $filename]);
}

echo json_encode(['success' => true, 'filename' => $filename]);
exit;


<?php
require_once '../includes/auth.php'; // Candidate authentication
require_once '../includes/db.php';

header('Content-Type: application/json');

// Check candidate session
if (!isset($_SESSION['candidate_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$candidateId = $_SESSION['candidate_id'];

// Validate POST params
$interviewId = $_POST['interview_id'] ?? null;
$questionId = $_POST['question_id'] ?? null;

if (!$interviewId || !$questionId) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

if (!isset($_FILES['video'])) {
    echo json_encode(['success' => false, 'error' => 'No video file uploaded']);
    exit;
}

$file = $_FILES['video'];

// Validate file type (allow webm/mp4/ogg)
$allowedMimeTypes = ['video/webm', 'video/mp4', 'video/ogg'];
if (!in_array($file['type'], $allowedMimeTypes)) {
    echo json_encode(['success' => false, 'error' => 'Invalid video format']);
    exit;
}

// Validate file size (max 50MB)
$maxSize = 50 * 1024 * 1024; // 50 MB
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'error' => 'File too large']);
    exit;
}

// Prepare upload directory
$uploadDir = __DIR__ . '/../uploads/answers/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('answer_', true) . '.' . $ext;
$targetFile = $uploadDir . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file']);
    exit;
}

// Optional: Save or update answer record with video filename
// You can defer this to the form submit step, but you can also store/update here if desired.
// For example:

// Check if answer exists
$sql = "SELECT id FROM answers WHERE interview_id = ? AND question_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$interviewId, $questionId]);

if ($stmt->rowCount()) {
    // Update existing answer with video filename only (do not overwrite text answer here)
    $answerId = $stmt->fetchColumn();
    $update = $pdo->prepare("UPDATE answers SET answer_video = ? WHERE id = ?");
    $update->execute([$filename, $answerId]);
} else {
    // Insert new answer record with video filename only
    $insert = $pdo->prepare("INSERT INTO answers (interview_id, question_id, answer_video) VALUES (?, ?, ?)");
    $insert->execute([$interviewId, $questionId, $filename]);
}

echo json_encode(['success' => true, 'filename' => $filename]);
exit;

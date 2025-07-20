<?php
require_once '../includes/auth_candidate.php';
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

// Ensure uploads directory exists
$uploadDir = __DIR__ . '/../uploads/answers/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Check for uploaded file
if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No video uploaded.']);
    exit;
}

$videoTmpPath = $_FILES['video']['tmp_name'];
$extension = '.webm';
$candidateId = $_SESSION['candidate_id'] ?? 'unknown';
$filename = 'answer_' . $candidateId . '_' . time() . $extension;
$targetPath = $uploadDir . $filename;

// Move uploaded file
if (!move_uploaded_file($videoTmpPath, $targetPath)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to save video.']);
    exit;
}

// OPTIONAL: Save reference to database (example schema assumed)
$stmt = $pdo->prepare("INSERT INTO candidate_answers (candidate_id, answer_type, file_path, submitted_at) VALUES (?, 'video', ?, NOW())");
$stmt->execute([$candidateId, 'uploads/answers/' . $filename]);

echo json_encode(['status' => 'success', 'message' => 'Video uploaded successfully.']);

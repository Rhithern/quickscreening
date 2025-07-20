<?php
require_once '../includes/auth_candidate.php';
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

// Validate required POST data
if (!isset($_POST['question_id']) || !isset($_POST['interview_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing question_id or interview_id.']);
    exit;
}

$questionId = intval($_POST['question_id']);
$interviewId = intval($_POST['interview_id']);
$candidateId = $_SESSION['candidate_id'] ?? 0;

if (!$candidateId) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
    exit;
}

// Ensure upload dir exists
$uploadDir = __DIR__ . '/../uploads/answers/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Validate uploaded file
if (!isset($_FILES['media']) || $_FILES['media']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No media uploaded.']);
    exit;
}

// Allow only specific file types
$allowedExtensions = ['webm', 'ogg', 'mp3', 'mp4', 'wav'];
$originalName = $_FILES['media']['name'];
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($extension, $allowedExtensions)) {
    http_response_code(415);
    echo json_encode(['status' => 'error', 'message' => 'Invalid file type.']);
    exit;
}

// Save file
$filename = "answer_{$candidateId}_{$questionId}_" . time() . '.' . $extension;
$targetPath = $uploadDir . $filename;

if (!move_uploaded_file($_FILES['media']['tmp_name'], $targetPath)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to save file.']);
    exit;
}

// Save to database
try {
    $stmt = $pdo->prepare("
        INSERT INTO candidate_answers (candidate_id, interview_id, question_id, answer_type, file_path, submitted_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $candidateId,
        $interviewId,
        $questionId,
        $extension, // file type
        'uploads/answers/' . $filename
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Media uploaded successfully.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}

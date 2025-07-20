<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/email.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'No video uploaded or upload error']);
    exit;
}

$candidateId = $_SESSION['user_id'];

// Validate and create uploads directory if not exists
$uploadDir = __DIR__ . '/../assets/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename for the video
$extension = pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
if (empty($extension)) {
    $extension = 'webm';  // default to webm if missing
}
$filename = uniqid('video_', true) . '.' . $extension;
$uploadFilePath = $uploadDir . $filename;

// Move uploaded file
if (!move_uploaded_file($_FILES['video']['tmp_name'], $uploadFilePath)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save uploaded video']);
    exit;
}

// Find the active interview for this candidate (status not completed)
$stmt = $pdo->prepare("SELECT id, position_id, admin_id FROM interviews WHERE candidate_id = ? AND status != 'completed' LIMIT 1");
$stmt->execute([$candidateId]);
$interview = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$interview) {
    // Delete uploaded video because no matching interview
    unlink($uploadFilePath);
    echo json_encode(['status' => 'error', 'message' => 'No active interview found']);
    exit;
}

// Update interview with video URL and mark completed
$stmt = $pdo->prepare("UPDATE interviews SET video_url = ?, status = 'completed', completed_at = NOW() WHERE id = ?");
$stmt->execute([$filename, $interview['id']]);

// Fetch candidate email, position title, admin email
$stmt = $pdo->prepare("SELECT c.email AS candidateEmail, p.title AS positionName, a.email AS adminEmail
                       FROM candidates c
                       JOIN positions p ON p.id = ?
                       JOIN admins a ON a.id = ?
                       WHERE c.id = ?");
$stmt->execute([$interview['position_id'], $interview['admin_id'], $candidateId]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($data) {
    sendInterviewCompletionNotification($data['candidateEmail'], $data['positionName'], $data['adminEmail']);
}

echo json_encode(['status' => 'success', 'message' => 'Video uploaded and interview completed']);
exit;

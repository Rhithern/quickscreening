<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $candidate_id = $_SESSION['candidate_id'] ?? 0;
    $interview_id = $_POST['interview_id'] ?? '';
    $question_id = $_POST['question_id'] ?? '';

    if ($candidate_id && isset($_FILES['answer'])) {
        $file = $_FILES['answer'];
        $allowed = ['video/mp4', 'video/webm', 'audio/ogg', 'audio/mpeg'];
        
        if (in_array($file['type'], $allowed)) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('answer_', true) . '.' . $ext;
            $path = '../uploads/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $path)) {
                // Save to DB
                $stmt = $pdo->prepare("INSERT INTO answers (candidate_id, interview_id, question_id, file_path, file_type, uploaded_at)
                                       VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$candidate_id, $interview_id, $question_id, $filename, $file['type']]);

                echo '/uploads/' . $filename; // Sent back to client for preview
                exit;
            } else {
                http_response_code(500);
                echo "Failed to move file";
                exit;
            }
        } else {
            http_response_code(400);
            echo "Unsupported file type";
            exit;
        }
    }
}

http_response_code(400);
echo "Invalid request";

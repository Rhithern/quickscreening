<?php
session_start();
$targetDir = "assets/uploads/";
$filename = uniqid() . ".webm";
$targetFile = $targetDir . $filename;

if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

if (move_uploaded_file($_FILES['video']['tmp_name'], $targetFile)) {
    echo "Success";
    // Optionally, save to DB: candidate_id, question_id, video path
} else {
    echo "Failed";
}
?>

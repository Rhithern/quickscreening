<?php
// video_stream.php

session_start();
require_once 'includes/auth.php';   // your auth system, check candidate/admin logged in
require_once 'includes/db.php';

if (!isset($_GET['video']) || empty($_GET['video'])) {
    http_response_code(400);
    exit('Invalid video parameter');
}

$videoFile = basename($_GET['video']); // sanitize input
$videoPath = __DIR__ . '/assets/uploads/' . $videoFile;

// Check if file exists
if (!file_exists($videoPath)) {
    http_response_code(404);
    exit('Video not found');
}

// Check user permissions:
// For example, if candidate, check this video belongs to them
// If admin, allow all

$userId = $_SESSION['user_id'];  // example, adjust based on your session
$userRole = $_SESSION['role'];   // 'admin' or 'candidate'

if ($userRole === 'candidate') {
    // Query DB to confirm candidate owns this video/interview
    $stmt = $pdo->prepare("
        SELECT i.id FROM interviews i
        JOIN candidates c ON i.candidate_id = c.id
        WHERE i.video_filename = ? AND c.user_id = ?
    ");
    $stmt->execute([$videoFile, $userId]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        exit('Access denied');
    }
} elseif ($userRole !== 'admin') {
    http_response_code(403);
    exit('Access denied');
}

// Serve video with proper headers and support for range requests (streaming)
$size = filesize($videoPath);
$length = $size;
$start = 0;
$end = $size - 1;
header('Content-Type: video/webm'); // Adjust MIME based on your videos
header("Accept-Ranges: 0-$length");

if (isset($_SERVER['HTTP_RANGE'])) {
    $range = $_SERVER['HTTP_RANGE'];
    list(, $range) = explode('=', $range, 2);
    if (strpos($range, ',') !== false) {
        header('HTTP/1.1 416 Requested Range Not Satisfiable');
        exit;
    }
    if ($range == '-') {
        $start = $size - substr($range, 1);
    } else {
        $range = explode('-', $range);
        $start = intval($range[0]);
        $end = (isset($range[1]) && is_numeric($range[1])) ? intval($range[1]) : $end;
    }
    if ($start > $end || $start > $size - 1 || $end >= $size) {
        header('HTTP/1.1 416 Requested Range Not Satisfiable');
        exit;
    }
    $length = $end - $start + 1;
    header('HTTP/1.1 206 Partial Content');
    header("Content-Range: bytes $start-$end/$size");
} else {
    header('HTTP/1.1 200 OK');
}

header("Content-Length: $length");

$fp = fopen($videoPath, 'rb');
fseek($fp, $start);
$bufferSize = 1024 * 8;
while (!feof($fp) && ($p = ftell($fp)) <= $end) {
    if ($p + $bufferSize > $end) {
        $bufferSize = $end - $p + 1;
    }
    set_time_limit(0);
    echo fread($fp, $bufferSize);
    flush();
}
fclose($fp);
exit;

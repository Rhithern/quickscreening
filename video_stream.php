<?php
// video_stream.php

session_start();
require_once 'includes/auth.php';   // Your auth checks for admin/candidate
require_once 'includes/db.php';

if (!isset($_GET['video']) || empty($_GET['video'])) {
    http_response_code(400);
    exit('Invalid video parameter.');
}

$videoFile = basename($_GET['video']); // sanitize input
$videoPath = __DIR__ . '/assets/uploads/' . $videoFile;

if (!file_exists($videoPath)) {
    http_response_code(404);
    exit('Video not found.');
}

// User info from session
$userId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? null;

if (!$userId || !$userRole) {
    http_response_code(401);
    exit('Unauthorized.');
}

// Authorization check
if ($userRole === 'candidate') {
    // Check candidate owns the interview video
    $stmt = $pdo->prepare("
        SELECT i.id FROM interviews i
        JOIN candidates c ON i.candidate_id = c.id
        WHERE i.video_filename = ? AND c.user_id = ?
    ");
    $stmt->execute([$videoFile, $userId]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        exit('Access denied.');
    }
} elseif ($userRole === 'admin') {
    // admins can access all videos
} else {
    http_response_code(403);
    exit('Access denied.');
}

// Serve video with support for HTTP Range (streaming)

$size = filesize($videoPath);
$length = $size;
$start = 0;
$end = $size - 1;
$mime = mime_content_type($videoPath) ?: 'application/octet-stream';

header("Content-Type: $mime");
header("Accept-Ranges: bytes");

if (isset($_SERVER['HTTP_RANGE'])) {
    $range = $_SERVER['HTTP_RANGE'];
    list(, $range) = explode('=', $range, 2);
    if (strpos($range, ',') !== false) {
        header('HTTP/1.1 416 Requested Range Not Satisfiable');
        exit;
    }
    if ($range == '-') {
        $start = $size - intval(substr($range, 1));
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
while (!feof($fp) && ($pos = ftell($fp)) <= $end) {
    if ($pos + $bufferSize > $end) {
        $bufferSize = $end - $pos + 1;
    }
    set_time_limit(0);
    echo fread($fp, $bufferSize);
    flush();
}
fclose($fp);
exit;

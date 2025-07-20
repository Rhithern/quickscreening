<?php
require_once '../includes/auth_admin.php';
require_once '../includes/db_connect.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: list_questions.php');
exit;

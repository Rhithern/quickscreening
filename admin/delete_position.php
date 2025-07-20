<?php
require_once '../includes/auth.php';  // Admin authentication
require_once '../includes/db.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    header('Location: list_positions.php?error=Invalid position ID');
    exit;
}

// Optional: check if position exists before deleting
$stmt = $pdo->prepare("SELECT * FROM positions WHERE id = ?");
$stmt->execute([$id]);
$position = $stmt->fetch();

if (!$position) {
    header('Location: list_positions.php?error=Position not found');
    exit;
}

// Delete the position
$stmt = $pdo->prepare("DELETE FROM positions WHERE id = ?");
$stmt->execute([$id]);

header('Location: list_positions.php?success=Position deleted successfully');
exit;

<?php
function getCandidateNotifications($pdo, $candidateId, $limit = 10, $unreadOnly = false) {
    $sql = "SELECT * FROM notifications WHERE candidate_id = ?";
    if ($unreadOnly) {
        $sql .= " AND is_read = 0";
    }
    $sql .= " ORDER BY created_at DESC LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$candidateId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function markNotificationRead($pdo, $notificationId, $candidateId) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND candidate_id = ?");
    $stmt->execute([$notificationId, $candidateId]);
}
?>

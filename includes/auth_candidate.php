<?php
// includes/auth_candidate.php
session_start();

if (!isset($_SESSION['candidate_id'])) {
    // Redirect to candidate login page if not logged in
    header('Location: /candidate/login.php');
    exit;
}
?>

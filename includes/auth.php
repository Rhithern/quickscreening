<?php
session_start();
if (!isset($_SESSION['user_type'])) {
    header('Location: /login.php');
    exit();
}
?>


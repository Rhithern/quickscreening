<?php
// includes/header.php
require_once __DIR__ . '/../config/config.php';
session_start();

// Determine user role for navbar logic
$userRole = $_SESSION['role'] ?? 'guest'; // 'admin', 'candidate', or 'guest'

$siteName = SITE_NAME; // from config.php
$logoUrl = '/assets/logo.png'; // default logo, customize later from branding
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($siteName) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="/assets/css/custom.css" rel="stylesheet" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4 shadow-sm">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="/">
      <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" height="30" class="me-2" />
      <span><?= htmlspecialchars($siteName) ?></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarMain">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <?php if ($userRole === 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="/admin/dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="/admin/list_positions.php">Positions</a></li>
          <li class="nav-item"><a class="nav-link" href="/admin/list_questions.php">Questions</a></li>
          <li class="nav-item"><a class="nav-link" href="/admin/send_invite.php">Send Invite</a></li>
          <li class="nav-item"><a class="nav-link" href="/admin/customize_brand.php">Branding</a></li>
          <li class="nav-item"><a class="nav-link" href="/admin/export_reports.php">Export Reports</a></li>
          <li class="nav-item"><a class="nav-link" href="/admin/logout.php">Logout</a></li>

        <?php elseif ($userRole === 'candidate'): ?>
          <li class="nav-item"><a class="nav-link" href="/candidate/dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="/candidate/logout.php">Logout</a></li>

        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="/login.php">Login</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="signupDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Sign Up
            </a>
            <ul class="dropdown-menu" aria-labelledby="signupDropdown">
              <li><a class="dropdown-item" href="/candidate/register.php">Candidate</a></li>
              <li><a class="dropdown-item" href="/admin/register.php">Admin</a></li>
            </ul>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container">


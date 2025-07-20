<?php
// includes/header.php
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars(SITE_NAME) ?><?= isset($pageTitle) ? ' - ' . htmlspecialchars($pageTitle) : '' ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="/assets/css/style.css">

</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
      <a class="navbar-brand" href="/"><?= htmlspecialchars(SITE_NAME) ?></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="/upload_video.php">Upload</a></li>
          <li class="nav-item"><a class="nav-link" href="/dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="/logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container">

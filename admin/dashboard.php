<?php
// admin/dashboard.php
session_start();
require_once '../includes/auth.php';   // Admin auth check - implement this to restrict access
require_once '../includes/db.php';
require_once '../config/config.php';

// Fetch stats from DB
try {
    $openPositions = $pdo->query("SELECT COUNT(*) FROM positions WHERE active = 1")->fetchColumn();
    $pendingInterviews = $pdo->query("SELECT COUNT(*) FROM interviews WHERE status = 'pending'")->fetchColumn();
    $completedInterviews = $pdo->query("SELECT COUNT(*) FROM interviews WHERE status = 'completed'")->fetchColumn();
} catch (Exception $e) {
    // handle errors gracefully
    $openPositions = $pendingInterviews = $completedInterviews = 0;
}

// Page title for header
$pageTitle = 'Dashboard';
include '../includes/header.php';
?>

<h1 class="mb-4">Admin Dashboard</h1>

<div class="row">
  <div class="col-md-4">
    <div class="card text-white bg-primary mb-3">
      <div class="card-body">
        <h5 class="card-title">Open Positions</h5>
        <p class="card-text fs-2"><?= $openPositions ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card text-white bg-warning mb-3">
      <div class="card-body">
        <h5 class="card-title">Pending Interviews</h5>
        <p class="card-text fs-2"><?= $pendingInterviews ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card text-white bg-success mb-3">
      <div class="card-body">
        <h5 class="card-title">Completed Interviews</h5>
        <p class="card-text fs-2"><?= $completedInterviews ?></p>
      </div>
    </div>
  </div>
</div>

<h2 class="mt-5 mb-3">Interview Statistics</h2>
<canvas id="interviewStats" width="400" height="200"></canvas>

<?php include '../includes/footer.php'; ?>

<script>
  const ctx = document.getElementById('interviewStats').getContext('2d');
  const interviewChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Open Positions', 'Pending Interviews', 'Completed Interviews'],
      datasets: [{
        label: 'Counts',
        data: [<?= $openPositions ?>, <?= $pendingInterviews ?>, <?= $completedInterviews ?>],
        backgroundColor: [
          'rgba(0, 123, 255, 0.7)',
          'rgba(255, 193, 7, 0.7)',   // Changed to warning yellow for consistency with card color
          'rgba(40, 167, 69, 0.7)'
        ],
        borderColor: [
          'rgba(0, 123, 255, 1)',
          'rgba(255, 193, 7, 1)',
          'rgba(40, 167, 69, 1)'
        ],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true, precision: 0 }
      }
    }
  });
</script>

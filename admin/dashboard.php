<?php
// admin/dashboard.php
$pageTitle = "Admin Dashboard";
include '../includes/header.php';
?>

<style>
  /* Sidebar styles */
  .sidebar {
    height: 100vh;
    position: fixed;
    top: 56px; /* height of navbar if any */
    left: 0;
    padding-top: 1rem;
    background-color: #f8f9fa;
    border-right: 1px solid #dee2e6;
    width: 250px;
  }

  main {
    margin-left: 250px;
    padding: 2rem;
  }

  @media (max-width: 767.98px) {
    .sidebar {
      position: static;
      height: auto;
      width: 100%;
      border-right: none;
    }
    main {
      margin-left: 0;
      padding: 1rem;
    }
  }
</style>

<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
  <div class="container-fluid">
    <button class="btn btn-primary d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle sidebar">
      ☰ Menu
    </button>
    <a class="navbar-brand ms-2" href="#"><?= htmlspecialchars(SITE_NAME) ?></a>
  </div>
</nav>

<div class="collapse d-md-block sidebar" id="sidebarMenu">
  <ul class="nav flex-column">
    <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
    <li class="nav-item"><a class="nav-link" href="create_position.php">Create Position</a></li>
    <li class="nav-item"><a class="nav-link" href="add_questions.php">Add Questions</a></li>
    <li class="nav-item"><a class="nav-link" href="list_positions.php">List Positions</a></li>
    <li class="nav-item"><a class="nav-link" href="send_invite.php">Send Interview Invite</a></li>
    <li class="nav-item"><a class="nav-link" href="customize_brand.php">Customize Branding</a></li>
    <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
  </ul>
</div>

<main>
  <h1>Welcome, Admin!</h1>
  <p>Use the sidebar to manage positions, questions, and interviews.</p>

  <!-- Example cards for stats -->
  <div class="row">
    <div class="col-md-4">
      <div class="card text-white bg-primary mb-3">
        <div class="card-body">
          <h5 class="card-title">Open Positions</h5>
          <p class="card-text fs-2">12</p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-white bg-success mb-3">
        <div class="card-body">
          <h5 class="card-title">Pending Interviews</h5>
          <p class="card-text fs-2">5</p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-white bg-info mb-3">
        <div class="card-body">
          <h5 class="card-title">Completed Interviews</h5>
          <p class="card-text fs-2">30</p>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include '../includes/footer.php'; ?>

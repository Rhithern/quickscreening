<?php
$pageTitle = "Admin Dashboard";
include '../includes/header.php';
?>

<div class="row">
  <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse" id="sidebarMenu">
    <div class="position-sticky pt-3">
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
  </nav>

  <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <h2>Welcome, Admin!</h2>
    <p>Use the sidebar to manage positions, questions, and interviews.</p>

    <!-- Add dashboard stats or charts here -->
  </main>
</div>

<?php include '../includes/footer.php'; ?>

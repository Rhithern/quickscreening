<?php
require_once '../includes/auth_admin.php';
require_once '../includes/db_connect.php';
$pageTitle = 'List Positions';
include '../includes/header.php';

// Fetch all positions
$stmt = $pdo->query("SELECT p.*, COUNT(a.id) AS applicant_count 
                     FROM positions p 
                     LEFT JOIN applications a ON p.id = a.position_id 
                     GROUP BY p.id 
                     ORDER BY p.created_at DESC");
$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Job Positions</h2>

<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>Title</th>
      <th>Description</th>
      <th>Created</th>
      <th>Applicants</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($positions as $pos): ?>
      <tr>
        <td><?= htmlspecialchars($pos['title']) ?></td>
        <td><?= htmlspecialchars(substr($pos['description'], 0, 60)) ?>...</td>
        <td><?= date('Y-m-d', strtotime($pos['created_at'])) ?></td>
        <td><?= $pos['applicant_count'] ?></td>
        <td>
          <a href="edit_position.php?id=<?= $pos['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
          <a href="delete_position.php?id=<?= $pos['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php include '../includes/footer.php'; ?>


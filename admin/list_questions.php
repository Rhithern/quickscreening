<?php
require_once '../includes/auth_admin.php';
require_once '../includes/db_connect.php';
$pageTitle = 'List Questions';
include '../includes/header.php';

// Fetch all questions with position titles
$sql = "SELECT q.id, q.question_text, p.title AS position_title 
        FROM questions q 
        LEFT JOIN positions p ON q.position_id = p.id 
        ORDER BY p.title, q.id";

$stmt = $pdo->query($sql);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Interview Questions</h2>

<a href="add_questions.php" class="btn btn-success mb-3">Add New Question</a>

<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>Position</th>
      <th>Question</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($questions)): ?>
      <tr><td colspan="3" class="text-center">No questions found.</td></tr>
    <?php else: ?>
      <?php foreach ($questions as $q): ?>
      <tr>
        <td><?= htmlspecialchars($q['position_title']) ?></td>
        <td><?= htmlspecialchars($q['question_text']) ?></td>
        <td>
          <a href="edit_question.php?id=<?= $q['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
          <a href="delete_question.php?id=<?= $q['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this question?')">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>

<?php include '../includes/footer.php'; ?>


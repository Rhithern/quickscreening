<?php
require_once '../includes/auth_admin.php';
require_once '../includes/db_connect.php';
$pageTitle = 'List Positions';
include '../includes/header.php';

// Fetch filter inputs
$search = $_GET['search'] ?? '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$sort = $_GET['sort'] ?? 'created_at';
$order = $_GET['order'] ?? 'desc';

$validSort = ['title', 'created_at', 'applicant_count'];
$sort = in_array($sort, $validSort) ? $sort : 'created_at';
$order = strtolower($order) === 'asc' ? 'ASC' : 'DESC';

// Build SQL with filters
$sql = "SELECT p.*, COUNT(a.id) AS applicant_count
        FROM positions p
        LEFT JOIN applications a ON p.id = a.position_id
        WHERE 1=1";

$params = [];

// Filter: Search by title
if (!empty($search)) {
    $sql .= " AND p.title LIKE :search";
    $params[':search'] = "%$search%";
}

// Filter: Date range
if (!empty($from)) {
    $sql .= " AND DATE(p.created_at) >= :from";
    $params[':from'] = $from;
}
if (!empty($to)) {
    $sql .= " AND DATE(p.created_at) <= :to";
    $params[':to'] = $to;
}

$sql .= " GROUP BY p.id ORDER BY $sort $order";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Job Positions</h2>

<form method="get" class="mb-3 row g-2">
  <div class="col-md-3">
    <input type="text" name="search" class="form-control" placeholder="Search Title" value="<?= htmlspecialchars($search) ?>">
  </div>
  <div class="col-md-2">
    <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>">
  </div>
  <div class="col-md-2">
    <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>">
  </div>
  <div class="col-md-2">
    <select name="sort" class="form-select">
      <option value="created_at" <?= $sort === 'created_at' ? 'selected' : '' ?>>Date Created</option>
      <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>Title</option>
      <option value="applicant_count" <?= $sort === 'applicant_count' ? 'selected' : '' ?>>Applicants</option>
    </select>
  </div>
  <div class="col-md-1">
    <select name="order" class="form-select">
      <option value="asc" <?= $order === 'ASC' ? 'selected' : '' ?>>ASC</option>
      <option value="desc" <?= $order === 'DESC' ? 'selected' : '' ?>>DESC</option>
    </select>
  </div>
  <div class="col-md-2">
    <button type="submit" class="btn btn-primary w-100">Apply</button>
  </div>
</form>

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

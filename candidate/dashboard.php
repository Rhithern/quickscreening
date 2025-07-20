<?php
session_start();
require_once '../includes/auth_candidate.php';
require_once '../includes/db.php';

$pageTitle = "Candidate Dashboard";

// Get candidate id from session
$candidateId = $_SESSION['candidate_id'] ?? null;
if (!$candidateId) {
    header('Location: /login.php');
    exit;
}

// Fetch candidate details (optional)
$stmt = $pdo->prepare("SELECT * FROM candidates WHERE id = ?");
$stmt->execute([$candidateId]);
$candidate = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$candidate) {
    echo '<div class="alert alert-danger">Candidate not found.</div>';
    include '../includes/footer.php';
    exit;
}

// Handle filters and pagination
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status_filter'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Build SQL filters
$where = " WHERE i.candidate_id = :candidate_id ";
$params = [':candidate_id' => $candidateId];

if ($search !== '') {
    $where .= " AND p.title LIKE :search ";
    $params[':search'] = '%' . $search . '%';
}

if (in_array($status_filter, ['pending', 'completed', 'cancelled'])) {
    $where .= " AND i.status = :status ";
    $params[':status'] = $status_filter;
}

// Count total for pagination
$countSql = "SELECT COUNT(*) FROM interviews i JOIN positions p ON i.position_id = p.id $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalInterviews = $countStmt->fetchColumn();

// Fetch interviews with limit and offset
$sql = "SELECT i.*, p.title AS position_title FROM interviews i JOIN positions p ON i.position_id = p.id
        $where ORDER BY i.created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

// Bind params dynamically
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$interviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch notifications (example query, adjust per your schema)
$notifStmt = $pdo->prepare("SELECT * FROM notifications WHERE candidate_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
$notifStmt->execute([$candidateId]);
$notifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<h2>Welcome, <?= htmlspecialchars($candidate['name'] ?? $candidate['email']) ?></h2>

<a href="edit_profile.php" class="btn btn-outline-secondary mb-3">Edit Profile</a>

<!-- Notifications Panel -->
<?php if (count($notifications) > 0): ?>
<div class="alert alert-info" role="alert" aria-live="polite" aria-atomic="true">
    <h5>Notifications (<?= count($notifications) ?> new)</h5>
    <ul>
        <?php foreach ($notifications as $notif): ?>
            <li><?= htmlspecialchars($notif['message']) ?> <small class="text-muted">(<?= date('Y-m-d H:i', strtotime($notif['created_at'])) ?>)</small></li>
        <?php endforeach; ?>
    </ul>
    <a href="notifications.php" class="btn btn-sm btn-primary mt-2">View All</a>
</div>
<?php endif; ?>

<!-- Filters -->
<form method="get" class="row g-2 mb-3" role="search" aria-label="Filter interviews">
  <div class="col-md-6">
    <input type="search" name="search" placeholder="Search Position" value="<?= htmlspecialchars($search) ?>" class="form-control" aria-label="Search by position title" />
  </div>
  <div class="col-md-4">
    <select name="status_filter" class="form-select" aria-label="Filter by interview status">
      <option value="">All Statuses</option>
      <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
      <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
      <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
    </select>
  </div>
  <div class="col-md-2">
    <button class="btn btn-primary w-100" type="submit">Filter</button>
  </div>
</form>

<?php if (empty($interviews)): ?>
    <div class="alert alert-info">You currently have no scheduled interviews.</div>
<?php else: ?>
<table class="table table-bordered table-hover table-responsive" aria-describedby="interviewTableDesc">
    <caption id="interviewTableDesc" class="visually-hidden">List of scheduled interviews with status and actions</caption>
    <thead class="table-light">
        <tr>
            <th scope="col">Position</th>
            <th scope="col">Scheduled On</th>
            <th scope="col">Time Left</th>
            <th scope="col">Status</th>
            <th scope="col" aria-label="Actions">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($interviews as $interview): ?>
            <?php
            $scheduled_at = $interview['scheduled_at'] ?? $interview['created_at'];
            $timeLeft = max(0, strtotime($scheduled_at) - time());
            ?>
            <tr>
                <td><?= htmlspecialchars($interview['position_title']) ?></td>
                <td><?= date('Y-m-d H:i', strtotime($scheduled_at)) ?></td>
                <td data-timestamp="<?= strtotime($scheduled_at) ?>" class="time-left-cell"></td>
                <td>
                    <?php
                    switch ($interview['status']) {
                        case 'completed': echo '<span class="badge bg-success">Completed</span>'; break;
                        case 'pending': echo '<span class="badge bg-warning text-dark">Pending</span>'; break;
                        case 'cancelled': echo '<span class="badge bg-danger">Cancelled</span>'; break;
                        default: echo '<span class="badge bg-secondary">Unknown</span>'; break;
                    }
                    ?>
                </td>
                <td>
                    <?php if ($interview['status'] === 'pending'): ?>
                        <a href="interview.php?id=<?= $interview['id'] ?>" class="btn btn-sm btn-primary" title="Take Interview">Take Interview</a>
                    <?php else: ?>
                        <a href="view_interview.php?id=<?= $interview['id'] ?>" class="btn btn-sm btn-secondary" title="View Submission">View Submission</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Pagination -->
<nav aria-label="Interview pagination">
  <ul class="pagination justify-content-center">
    <?php
    $totalPages = ceil($totalInterviews / $perPage);
    $queryParams = $_GET;
    for ($p = 1; $p <= $totalPages; $p++):
        $queryParams['page'] = $p;
        $url = $_SERVER['PHP_SELF'] . '?' . http_build_query($queryParams);
    ?>
    <li class="page-item <?= $p == $page ? 'active' : '' ?>">
      <a class="page-link" href="<?= $url ?>"><?= $p ?></a>
    </li>
    <?php endfor; ?>
  </ul>
</nav>
<?php endif; ?>

<a href="logout.php" class="btn btn-outline-danger mt-4">Logout</a>

<script>
  // Countdown Timer for each "Time Left" cell
  function formatTime(seconds) {
    if (seconds <= 0) return "Started";
    let d = Math.floor(seconds / (3600*24));
    let h = Math.floor((seconds % (3600*24)) / 3600);
    let m = Math.floor((seconds % 3600) / 60);
    let s = seconds % 60;
    let str = "";
    if(d > 0) str += d + "d ";
    if(h > 0 || d > 0) str += h + "h ";
    if(m > 0 || h > 0 || d > 0) str += m + "m ";
    str += s + "s";
    return str;
  }

  function updateTimers() {
    document.querySelectorAll('.time-left-cell').forEach(cell => {
      let ts = parseInt(cell.getAttribute('data-timestamp'));
      let secondsLeft = Math.floor((ts - Date.now()/1000));
      cell.textContent = formatTime(secondsLeft);
    });
  }

  setInterval(updateTimers, 1000);
  updateTimers();
</script>

<?php include '../includes/footer.php'; ?>

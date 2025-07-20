<?php
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../vendor/autoload.php'; // For PHPMailer
require_once __DIR__ . '/../config.php';

$pageTitle = 'Export Reports';

// Handle filter submission
$positionFilter = $_GET['position'] ?? '';
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';

// Fetch positions for filter dropdown
$positions = $pdo->query("SELECT DISTINCT title FROM positions")->fetchAll(PDO::FETCH_COLUMN);

// Build SQL with optional filters
$sql = "SELECT i.id, i.candidate_name, i.email, i.position_applied, i.score, i.completed_at 
        FROM interviews i WHERE 1=1";
$params = [];

if (!empty($positionFilter)) {
    $sql .= " AND i.position_applied = :position";
    $params['position'] = $positionFilter;
}
if (!empty($fromDate)) {
    $sql .= " AND i.completed_at >= :from_date";
    $params['from_date'] = $fromDate . " 00:00:00";
}
if (!empty($toDate)) {
    $sql .= " AND i.completed_at <= :to_date";
    $params['to_date'] = $toDate . " 23:59:59";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$interviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Export as CSV and email if requested
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $filename = 'interview_report_' . date('Ymd_His') . '.csv';
    $filepath = __DIR__ . '/../exports/' . $filename;

    $fp = fopen($filepath, 'w');
    fputcsv($fp, array_keys($interviews[0] ?? []));
    foreach ($interviews as $row) {
        fputcsv($fp, $row);
    }
    fclose($fp);

    // Send email with CSV attachment
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->setFrom(ADMIN_EMAIL, SITE_NAME);
        $mail->addAddress(ADMIN_EMAIL);
        $mail->Subject = 'Interview Report - ' . date('Y-m-d');
        $mail->Body = "Hi Admin,\n\nAttached is the exported interview report.\n\nRegards,\nHR Bot";
        $mail->addAttachment($filepath);
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->send();
        $emailStatus = "✅ Report emailed to admin.";
    } catch (Exception $e) {
        $emailStatus = "❌ Email sending failed: {$mail->ErrorInfo}";
    }

    // Output file for download
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    readfile($filepath);
    exit;
}
?>

<h2>Export Interview Reports</h2>

<form method="get" class="row g-3 mb-4">
  <div class="col-md-4">
    <label for="position" class="form-label">Filter by Position</label>
    <select name="position" id="position" class="form-select">
      <option value="">-- All Positions --</option>
      <?php foreach ($positions as $pos): ?>
        <option value="<?= htmlspecialchars($pos) ?>" <?= $pos == $positionFilter ? 'selected' : '' ?>><?= htmlspecialchars($pos) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3">
    <label for="from_date" class="form-label">From Date</label>
    <input type="date" name="from_date" value="<?= htmlspecialchars($fromDate) ?>" class="form-control">
  </div>
  <div class="col-md-3">
    <label for="to_date" class="form-label">To Date</label>
    <input type="date" name="to_date" value="<?= htmlspecialchars($toDate) ?>" class="form-control">
  </div>
  <div class="col-md-2 d-grid">
    <label class="form-label">&nbsp;</label>
    <button type="submit" name="export" value="csv" class="btn btn-primary">Export CSV & Email</button>
  </div>
</form>

<?php if (!empty($emailStatus)): ?>
  <div class="alert alert-info"><?= htmlspecialchars($emailStatus) ?></div>
<?php endif; ?>

<?php if (count($interviews)): ?>
  <div class="table-responsive">
    <table class="table table-bordered table-sm">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Position</th>
          <th>Score</th>
          <th>Completed At</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($interviews as $interview): ?>
          <tr>
            <td><?= $interview['id'] ?></td>
            <td><?= htmlspecialchars($interview['candidate_name']) ?></td>
            <td><?= htmlspecialchars($interview['email']) ?></td>
            <td><?= htmlspecialchars($interview['position_applied']) ?></td>
            <td><?= $interview['score'] ?></td>
            <td><?= $interview['completed_at'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php else: ?>
  <p>No interview records found for the selected criteria.</p>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

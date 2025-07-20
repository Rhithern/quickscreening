<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth_admin.php';

$pageTitle = "Export Reports";
require_once __DIR__ . '/../includes/header.php';

// Handle export request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export'])) {
    $format = $_POST['format'] ?? 'csv';

    // Fetch applications
    $stmt = $pdo->query("SELECT a.id, a.full_name, a.email, a.status, a.score, a.submission_time, p.title AS position
                         FROM applications a
                         JOIN positions p ON a.position_id = p.id
                         ORDER BY a.submission_time DESC");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="interview_reports.csv"');
        $output = fopen('php://output', 'w');

        if (!empty($rows)) {
            fputcsv($output, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($output, $row);
            }
        } else {
            fputcsv($output, ['No data available']);
        }
        fclose($output);
        exit;

    } elseif ($format === 'xls') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="interview_reports.xls"');

        echo "<table border='1'>";
        if (!empty($rows)) {
            echo "<tr>";
            foreach (array_keys($rows[0]) as $col) {
                echo "<th>" . htmlspecialchars($col) . "</th>";
            }
            echo "</tr>";

            foreach ($rows as $row) {
                echo "<tr>";
                foreach ($row as $cell) {
                    echo "<td>" . htmlspecialchars($cell) . "</td>";
                }
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No data available</td></tr>";
        }
        echo "</table>";
        exit;
    }
}
?>

<div class="card">
  <div class="card-body">
    <h2 class="mb-3">Export Interview Reports</h2>
    <form method="post">
      <div class="mb-3">
        <label for="format" class="form-label">Choose Export Format:</label>
        <select name="format" id="format" class="form-select">
          <option value="csv">CSV</option>
          <option value="xls">Excel (.xls)</option>
        </select>
      </div>
      <button type="submit" name="export" class="btn btn-success">Export</button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4">Lab Reports</h1>
  <a class="btn btn-primary" href="<?= APP_URL ?>/lab/create">Add Report</a>
</div>
<div class="card">
  <div class="table-responsive">
    <table class="table table-striped mb-0">
      <thead><tr><th>Patient</th><th>Test</th><th>Result</th><th>Reported</th></tr></thead>
      <tbody>
      <?php foreach($items as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['first_name'].' '.$r['last_name']) ?></td>
          <td><?= htmlspecialchars($r['test_name']) ?></td>
          <td><?= htmlspecialchars($r['result_value'] ?? '') ?></td>
          <td><?= htmlspecialchars($r['reported_at'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

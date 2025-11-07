<h1 class="h4 mb-3">Recent Lab Results</h1>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>Patient</th><th>Test</th><th>Result</th><th>Reported At</th></tr></thead>
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

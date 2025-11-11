<h1 class="h4 mb-3">Lab · Completed</h1>
<div class="mb-2">
  <a class="btn btn-outline-secondary btn-sm" href="<?= APP_URL ?>/lab/search">Search</a>
  <a class="btn btn-outline-secondary btn-sm" href="<?= APP_URL ?>/lab/pending">Pending</a>
</div>

<div class="card">
  <table class="table mb-0">
    <thead><tr><th>#Report</th><th>Patient</th><th>Code</th><th>Test</th><th>Reported By</th><th>Reported At</th><th class="text-end">Action</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['report_id'] ?></td>
          <td><?= htmlspecialchars(($r['first_name']??'').' '.($r['last_name']??'')) ?></td>
          <td><?= htmlspecialchars($r['code'] ?? '—') ?></td>
          <td><?= htmlspecialchars($r['test_name']) ?></td>
          <td><?= htmlspecialchars($r['reported_by_name'] ?? '—') ?></td>
          <td><?= htmlspecialchars($r['reported_at'] ?? '—') ?></td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary"
               href="<?= APP_URL ?>/lab/report?id=<?= (int)$r['report_id'] ?>">
              View
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?>
        <tr><td colspan="7" class="text-muted">No completed results yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

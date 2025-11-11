<h1 class="h4 mb-3">Lab · Pending Orders</h1>
<div class="mb-2">
  <a class="btn btn-outline-secondary btn-sm" href="<?= APP_URL ?>/lab/search">Search</a>
  <a class="btn btn-outline-secondary btn-sm" href="<?= APP_URL ?>/lab/completed">Completed</a>
</div>

<div class="card">
  <table class="table mb-0">
    <thead><tr><th>#</th><th>Patient</th><th>Code</th><th>Test</th><th>Requested</th><th class="text-end">Action</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?= htmlspecialchars(($r['first_name']??'').' '.($r['last_name']??'')) ?></td>
          <td><?= htmlspecialchars($r['code'] ?? '—') ?></td>
          <td><?= htmlspecialchars($r['test_name']) ?></td>
          <td><?= htmlspecialchars($r['created_at']) ?></td>
          <td class="text-end">
            <a class="btn btn-sm btn-primary"
               href="<?= APP_URL ?>/lab/report-from-order?order_id=<?= (int)$r['id'] ?>">
              Add Results
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?>
        <tr><td colspan="6" class="text-muted">No pending orders.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

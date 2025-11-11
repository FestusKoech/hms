<?php $p = $patient; ?>
<div class="d-flex align-items-center justify-content-between mb-2">
  <h1 class="h5 mb-0">
    <?= htmlspecialchars(($p['first_name']??'').' '.($p['last_name']??'')) ?>
    <span class="text-muted">· <?= htmlspecialchars($p['code'] ?? '') ?></span>
  </h1>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="<?= APP_URL ?>/lab/search">Back</a>
  </div>
</div>

<div class="card">
  <div class="card-header">Lab Orders</div>
  <table class="table mb-0">
    <thead><tr><th>#</th><th>Test</th><th>Status</th><th>Requested</th><th class="text-end">Action</th></tr></thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td><?= (int)$o['id'] ?></td>
          <td><?= htmlspecialchars($o['test_name']) ?></td>
          <td>
            <span class="badge bg-<?= $o['status']==='reported'?'success':'secondary' ?>">
              <?= htmlspecialchars($o['status']) ?>
            </span>
          </td>
          <td><?= htmlspecialchars($o['created_at']) ?></td>
          <td class="text-end">
            <?php if ($o['status']==='ordered'): ?>
              <a class="btn btn-sm btn-primary"
                 href="<?= APP_URL ?>/lab/report-from-order?order_id=<?= (int)$o['id'] ?>">
                Add Results
              </a>
            <?php else: ?>
              <a class="btn btn-sm btn-outline-primary"
                 href="<?= APP_URL ?>/lab/report?id=<?= (int)$o['id'] ?>">View Report</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($orders)): ?>
        <tr><td colspan="5" class="text-muted">No lab orders for this patient.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

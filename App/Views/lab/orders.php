<?php if(!empty($_SESSION['flash'])): ?>
  <div class="alert alert-success small"><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
<?php endif; ?>

<h1 class="h4 mb-3">Pending Lab Orders</h1>
<div class="card">
  <div class="table-responsive">
    <table class="table table-striped mb-0">
      <thead><tr><th>#</th><th>Patient</th><th>Test</th><th>Ordered By</th><th>When</th><th></th></tr></thead>
      <tbody>
      <?php foreach($items as $o): ?>
        <tr>
          <td><?= (int)$o['id'] ?></td>
          <td><?= htmlspecialchars($o['first_name'].' '.$o['last_name']) ?></td>
          <td><?= htmlspecialchars($o['test_name']) ?></td>
          <td><?= htmlspecialchars($o['doctor']) ?></td>
          <td class="text-muted"><?= htmlspecialchars($o['created_at']) ?></td>
          <td class="text-end">
  <a class="btn btn-sm btn-primary"
     href="<?= APP_URL ?>/lab/report-from-order?order_id=<?= (int)$o['id'] ?>">
    Add Results
  </a>
</td>
          <div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Pending Lab Orders</h1>
  <a class="btn btn-outline-secondary" href="<?= APP_URL ?>/doctor/lab-reports">Recent Reports</a>
</div>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

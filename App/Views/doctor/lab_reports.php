<style>
.row-actions { opacity:0; transition:opacity .15s; }
.table tbody tr:hover .row-actions { opacity:1; }
.badge-soft { background:#f2f4f7; border:1px solid #e6e6ea; color:#333; }
</style>

<h1 class="h4 mb-3">Recent Lab Activity</h1>

<div class="card mb-4">
  <div class="card-header"><strong>Pending (Orders)</strong></div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>#</th><th>Patient</th><th>Test</th><th>Ordered By</th><th>When</th><th class="text-end">Status</th></tr></thead>
      <tbody>
      <?php foreach($pending as $o): ?>
        <tr>
          <td><?= (int)$o['id'] ?></td>
          <td><?= htmlspecialchars($o['first_name'].' '.$o['last_name']) ?></td>
          <td><?= htmlspecialchars($o['test_name']) ?></td>
          <td><?= htmlspecialchars($o['doctor']) ?></td>
          <td class="text-muted"><?= htmlspecialchars($o['created_at']) ?></td>
          <td class="text-end"><span class="badge badge-soft">Pending</span></td>
        </tr>
      <?php endforeach; ?>
      <?php if(empty($pending)): ?>
        <tr><td colspan="6" class="text-center text-muted py-3">No pending lab orders.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card">
  <div class="card-header"><strong>Completed (Reports)</strong></div>
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead><tr><th>#</th><th>Patient</th><th>Test</th><th>Result</th><th>Reported At</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach($completed as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?= htmlspecialchars($r['first_name'].' '.$r['last_name']) ?></td>
          <td><?= htmlspecialchars($r['test_name']) ?></td>
          <td><?= htmlspecialchars($r['result_value'] ?? '') ?></td>
          <td class="text-muted"><?= htmlspecialchars($r['reported_at'] ?? '') ?></td>
          <td class="text-end">
            <div class="row-actions">
              <a class="btn btn-sm btn-outline-secondary" href="<?= APP_URL ?>/doctor/lab-report?id=<?= (int)$r['id'] ?>">View</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if(empty($completed)): ?>
        <tr><td colspan="6" class="text-center text-muted py-3">No completed reports yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

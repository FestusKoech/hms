<?php if(!empty($_SESSION['flash'])): ?>
  <div class="alert alert-success small"><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
<?php endif; ?>

<h1 class="h4 mb-3">Lab Orders</h1>

<form method="get" class="row g-2 mb-3">
  <div class="col-auto">
    <select name="status" class="form-select">
      <option value="">All</option>
      <?php foreach (['ordered','pending','in_progress','completed','reported'] as $st): ?>
        <option value="<?= $st ?>" <?= ($st===($status??''))?'selected':'' ?>><?= ucfirst(str_replace('_',' ', $st)) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col">
    <input name="q" value="<?= htmlspecialchars($q??'') ?>" class="form-control" placeholder="Search patient or test">
  </div>
  <div class="col-auto">
    <button class="btn btn-primary">Filter</button>
  </div>
</form>

<div class="table-responsive">
<table class="table table-sm align-middle">
  <thead><tr>
    <th>When</th><th>Patient</th><th>Test</th><th>Status</th><th class="text-end">Actions</th>
  </tr></thead>
  <tbody>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><?= htmlspecialchars($r['created_at'] ?? '') ?></td>
      <td><?= htmlspecialchars(($r['first_name'] ?? '').' '.($r['last_name'] ?? '')) ?> <span class="text-muted">(<?= htmlspecialchars($r['patient_code'] ?? '') ?>)</span></td>
      <td><?= htmlspecialchars($r['test_name'] ?? '') ?></td>
      <td><span class="badge bg-light text-dark"><?= htmlspecialchars($r['status'] ?? '') ?></span></td>
      <td class="text-end">
        <a class="btn btn-sm btn-outline-secondary"
           href="<?= APP_URL ?>/doctor/lab-result?order_id=<?= (int)$r['id'] ?>">
          View
        </a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<?php if(($pages??1) > 1): ?>
<nav>
  <ul class="pagination pagination-sm">
    <?php for($i=1;$i<=$pages;$i++): ?>
      <li class="page-item <?= ($i==($page??1))?'active':'' ?>">
        <a class="page-link" href="?status=<?= urlencode($status??'') ?>&q=<?= urlencode($q??'') ?>&page=<?= $i ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>
<?php endif; ?>

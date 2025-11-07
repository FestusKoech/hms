<?php if(!empty($_SESSION['flash'])): ?>
  <div class="alert alert-success small"><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
<?php endif; ?>

<h1 class="h5 mb-3">Prescriptions (All)</h1>
<div class="card mb-4">
  <div class="table-responsive">
    <table class="table table-striped mb-0">
      <thead><tr><th>Patient</th><th>Drug</th><th>Days</th><th>Status</th><th class="text-end">Action</th></tr></thead>
      <tbody>
      <?php foreach($items as $i): ?>
        <tr>
          <td><?= htmlspecialchars($i['first_name'].' '.$i['last_name']) ?></td>
          <td><?= htmlspecialchars($i['drug']) ?></td>
          <td><?= (int)$i['duration_days'] ?></td>
          <td>
            <?php if((int)$i['dispensed']===1): ?>
              <span class="badge text-bg-success">Dispensed</span>
            <?php else: ?>
              <span class="badge text-bg-warning">Pending</span>
            <?php endif; ?>
          </td>
          <td class="text-end">
            <form method="post" action="<?= APP_URL ?>/pharmacy/fulfill" class="d-inline">
              <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="item_id" value="<?= (int)$i['item_id'] ?>">
              <input type="number" name="qty" class="form-control form-control-sm d-inline-block" style="width:90px" value="1" min="1">
              <button class="btn btn-sm btn-primary">Dispense</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<h2 class="h6 mb-2">Recent Dispense History</h2>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>#</th><th>Patient</th><th>Drug</th><th>Qty</th><th>When</th></tr></thead>
      <tbody>
      <?php foreach(($history ?? []) as $h): ?>
        <tr>
          <td><?= (int)$h['id'] ?></td>
          <td><?= htmlspecialchars($h['first_name'].' '.$h['last_name']) ?></td>
          <td><?= htmlspecialchars($h['drug']) ?></td>
          <td><?= (int)$h['qty'] ?></td>
          <td class="text-muted"><?= htmlspecialchars($h['dispensed_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

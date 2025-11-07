<h1 class="h4 mb-3">Fulfill Prescriptions</h1>
<div class="card">
  <div class="table-responsive">
    <table class="table table-striped mb-0">
      <thead><tr><th>Patient</th><th>Drug</th><th>Days</th><th></th></tr></thead>
      <tbody>
      <?php foreach($items as $i): ?>
        <tr>
          <td><?= htmlspecialchars($i['first_name'].' '.$i['last_name']) ?></td>
          <td><?= htmlspecialchars($i['drug']) ?></td>
          <td><?= (int)$i['duration_days'] ?></td>
          <td class="text-end">
            <form method="post" action="<?= APP_URL ?>/pharmacy/fulfill">
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

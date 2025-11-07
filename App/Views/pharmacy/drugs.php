<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4">Drugs</h1>
  <a class="btn btn-primary" href="<?= APP_URL ?>/pharmacy/drugs/create">Add Drug</a>
</div>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>SKU</th><th>Name</th><th>Form</th><th>Strength</th><th>On Hand</th><th>Reorder</th></tr></thead>
      <tbody>
      <?php foreach($items as $d): ?>
        <tr>
          <td><?= htmlspecialchars($d['sku']) ?></td>
          <td><?= htmlspecialchars($d['name']) ?></td>
          <td><?= htmlspecialchars($d['form'] ?? '') ?></td>
          <td><?= htmlspecialchars($d['strength'] ?? '') ?></td>
          <td><?= (int)$d['qty_on_hand'] ?></td>
          <td><?= (int)$d['reorder_level'] ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

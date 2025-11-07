<h1 class="h4 mb-3">Schedule Lab Test</h1>
<form method="post" action="<?= APP_URL ?>/doctor/lab-order" class="card card-body">
  <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
  <input type="hidden" name="patient_id" value="<?= (int)$patient_id ?>">
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Test</label>
      <select class="form-select" name="test_id" required>
        <?php foreach($tests as $t): ?>
          <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="mt-3"><button class="btn btn-primary">Create Order</button></div>
</form>

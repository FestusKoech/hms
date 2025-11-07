<h1 class="h4 mb-3">New Lab Report</h1>
<form method="post" action="<?= APP_URL ?>/lab/store" class="card card-body">
  <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
  <div class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Patient</label>
      <select class="form-select" name="patient_id" required>
        <?php foreach($patients as $p): ?>
          <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['first_name'].' '.$p['last_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Lab Test</label>
      <select class="form-select" name="test_id" required>
        <?php foreach($tests as $t): ?>
          <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Ordered By (Doctor ID)</label>
      <input class="form-control" type="number" name="ordered_by" required>
    </div>
    <div class="col-md-3"><label class="form-label">Result (value)</label><input class="form-control" name="result_value"></div>
    <div class="col-md-9"><label class="form-label">Result (text)</label><input class="form-control" name="result_text"></div>
  </div>
  <div class="mt-3"><button class="btn btn-primary">Save Report</button></div>
</form>

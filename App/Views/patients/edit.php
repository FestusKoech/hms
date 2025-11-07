<h1 class="h4 mb-3">Edit Patient</h1>
<form method="post" action="<?= APP_URL ?>/patients/update" class="card card-body">
  <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
  <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
  <div class="row g-3">
    <div class="col-md-3"><label class="form-label">Patient No</label><input name="patient_no" class="form-control" value="<?= htmlspecialchars($p['patient_no']) ?>" required></div>
    <div class="col-md-4"><label class="form-label">First name</label><input name="first_name" class="form-control" value="<?= htmlspecialchars($p['first_name']) ?>" required></div>
    <div class="col-md-5"><label class="form-label">Last name</label><input name="last_name" class="form-control" value="<?= htmlspecialchars($p['last_name']) ?>" required></div>
    <div class="col-md-3"><label class="form-label">DOB</label><input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($p['dob'] ?? '') ?>"></div>
    <div class="col-md-3"><label class="form-label">Sex</label>
      <select name="sex" class="form-select">
        <?php foreach(['','M','F','O'] as $s): ?>
          <option value="<?= $s ?>" <?= ($p['sex']??'')===$s?'selected':'' ?>><?= $s===''?'-':$s ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3"><label class="form-label">Contact</label><input name="contact" class="form-control" value="<?= htmlspecialchars($p['contact'] ?? '') ?>"></div>
    <div class="col-md-3"><label class="form-label">Emergency Contact</label><input name="emergency_contact" class="form-control" value="<?= htmlspecialchars($p['emergency_contact'] ?? '') ?>"></div>
    <div class="col-12"><label class="form-label">Address</label><input name="address" class="form-control" value="<?= htmlspecialchars($p['address'] ?? '') ?>"></div>
  </div>
  <div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary">Save</button>
    <a class="btn btn-outline-light" href="<?= APP_URL ?>/patients">Cancel</a>
  </div>
</form>

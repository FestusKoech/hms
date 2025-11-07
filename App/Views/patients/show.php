<h1 class="h4 mb-3">Patient · <?= htmlspecialchars($p['first_name'].' '.$p['last_name']) ?> (<?= htmlspecialchars($p['patient_no']) ?>)</h1>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header"><strong>Details</strong></div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-4">DOB</dt><dd class="col-8"><?= htmlspecialchars($p['dob'] ?? '-') ?></dd>
          <dt class="col-4">Sex</dt><dd class="col-8"><?= htmlspecialchars($p['sex'] ?? '-') ?></dd>
          <dt class="col-4">Contact</dt><dd class="col-8"><?= htmlspecialchars($p['contact'] ?? '-') ?></dd>
          <dt class="col-4">Address</dt><dd class="col-8"><?= htmlspecialchars($p['address'] ?? '-') ?></dd>
          <dt class="col-4">Emergency</dt><dd class="col-8"><?= htmlspecialchars($p['emergency_contact'] ?? '-') ?></dd>
        </dl>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card">
      <div class="card-header"><strong>Quick Actions</strong></div>
      <div class="card-body d-grid gap-2">
        <?php if(in_array(($role ?? ''),['doctor','admin'])): ?>
          <a class="btn btn-primary" href="<?= APP_URL ?>/doctor/prescribe?patient_id=<?= (int)$p['id'] ?>">Add Prescription</a>
          <a class="btn btn-outline-secondary" href="<?= APP_URL ?>/doctor/lab-order?patient_id=<?= (int)$p['id'] ?>">Schedule Lab Test</a>
          <a class="btn btn-outline-secondary" href="<?= APP_URL ?>/doctor/patient-report?patient_id=<?= (int)$p['id'] ?>">Add Patient Report</a>
        <?php endif; ?>
        <a class="btn btn-outline-light" href="<?= APP_URL ?>/patients">Back to Patients</a>
      </div>
    </div>
  </div>
</div>

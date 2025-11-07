<h1 class="h4 mb-3">Patient · <?= htmlspecialchars($p['first_name'].' '.$p['last_name']) ?> (<?= htmlspecialchars($p['patient_no']) ?>)</h1>

<div class="row g-3 mb-3">
  <div class="col-md-3"><a class="btn btn-primary w-100" href="<?= APP_URL ?>/doctor/prescribe?patient_id=<?= (int)$p['id'] ?>">Add Prescription</a></div>
  <div class="col-md-3"><a class="btn btn-outline-secondary w-100" href="<?= APP_URL ?>/doctor/lab-order?patient_id=<?= (int)$p['id'] ?>">Schedule Lab Test</a></div>
  <div class="col-md-3"><a class="btn btn-outline-secondary w-100" href="<?= APP_URL ?>/doctor/patient-report?patient_id=<?= (int)$p['id'] ?>">Add Patient Report</a></div>
  <div class="col-md-3"><a class="btn btn-outline-secondary w-100" href="<?= APP_URL ?>/doctor/lab-reports">All Lab Reports</a></div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header"><strong>Prescriptions</strong></div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>#</th><th>By</th><th>Date</th></tr></thead>
          <tbody>
            <?php foreach($prescriptions as $r): ?>
              <tr><td><?= (int)$r['id'] ?></td><td><?= htmlspecialchars($r['doctor']) ?></td><td><?= htmlspecialchars($r['created_at']) ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header"><strong>Lab Reports</strong></div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Test</th><th>Result</th><th>Reported</th></tr></thead>
          <tbody>
            <?php foreach($lab_reports as $r): ?>
              <tr><td><?= htmlspecialchars($r['test_name']) ?></td><td><?= htmlspecialchars($r['result_value'] ?? '') ?></td><td><?= htmlspecialchars($r['reported_at'] ?? '') ?></td></tr>
            <div class="col-lg-6">
  <div class="card mb-3">
    <div class="card-header"><strong>Pending Lab Orders</strong></div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead><tr><th>Test</th><th>Ordered</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach(($pending_lab ?? []) as $o): ?>
            <tr>
              <td><?= htmlspecialchars($o['test_name']) ?></td>
              <td class="text-muted"><?= htmlspecialchars($o['created_at']) ?></td>
              <td><span class="badge text-bg-warning">Pending</span></td>
            </tr>
          <?php endforeach; ?>
          <?php if(empty($pending_lab ?? [])): ?>
            <tr><td colspan="3" class="text-center text-muted py-3">No pending orders.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><strong>Lab Reports</strong></div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead><tr><th>Test</th><th>Result</th><th>Reported</th><th class="text-end">Action</th></tr></thead>
        <tbody>
          <?php foreach($lab_reports as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['test_name']) ?></td>
              <td><?= htmlspecialchars($r['result_value'] ?? '') ?></td>
              <td class="text-muted"><?= htmlspecialchars($r['reported_at'] ?? '') ?></td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-secondary" href="<?= APP_URL ?>/doctor/lab-report?id=<?= (int)$r['id'] ?>">View</a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if(empty($lab_reports)): ?>
            <tr><td colspan="4" class="text-center text-muted py-3">No completed reports yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

            
              <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-12">
    <div class="card">
      <div class="card-header"><strong>Patient Reports (Doctor Notes)</strong></div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Title</th><th>By</th><th>Date</th></tr></thead>
          <tbody>
            <?php foreach($patient_reports as $n): ?>
              <tr><td><?= htmlspecialchars($n['title']) ?></td><td><?= htmlspecialchars($n['doctor']) ?></td><td><?= htmlspecialchars($n['created_at']) ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

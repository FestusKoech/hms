<?php
/** Expected $r from controller:
 *  - id, patient_id, test_id, test_name
 *  - result_value, result_text
 *  - ordered_by, ordered_by_name
 *  - reported_by, reported_by_name, reported_at
 *  - first_name, last_name (patient)
 */
$patientName = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
$patientName = $patientName !== '' ? $patientName : 'Unknown patient';
$reportedAt  = !empty($r['reported_at']) ? $r['reported_at'] : '—';
?>

<!-- Report header (patient + test) -->
<div class="card mb-3">
  <div class="card-body d-flex justify-content-between align-items-center">
    <div>
      <div class="fw-semibold"><?= htmlspecialchars($patientName) ?></div>
      <div class="small text-muted">Test: <?= htmlspecialchars($r['test_name'] ?? '—') ?></div>
    </div>
    <div class="text-end">
      <div class="small text-muted">Report ID: #<?= (int)$r['id'] ?></div>
      <div class="small">Reported: <?= htmlspecialchars($reportedAt) ?></div>
    </div>
  </div>
</div>

<!-- Findings -->
<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card h-100">
      <div class="card-header">Result (Value)</div>
      <div class="card-body">
        <div class="fs-5"><?= htmlspecialchars($r['result_value'] ?? '—') ?></div>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-6">
    <div class="card h-100">
      <div class="card-header">Findings (Text)</div>
      <div class="card-body">
        <div style="white-space:pre-wrap"><?= htmlspecialchars($r['result_text'] ?? '—') ?></div>
      </div>
    </div>
  </div>
</div>

<!-- Provenance: who ordered / who reported -->
<div class="row g-3 mt-1">
  <div class="col-12 col-lg-6">
    <div class="card h-100">
      <div class="card-header">Ordered By</div>
      <div class="card-body">
        <div><?= htmlspecialchars($r['ordered_by_name'] ?? '—') ?></div>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-6">
    <div class="card h-100">
      <div class="card-header">Reported By</div>
      <div class="card-body">
        <div><?= htmlspecialchars($r['reported_by_name'] ?? '—') ?></div>
      </div>
    </div>
  </div>
</div>

<!-- Actions -->
<div class="mt-3 d-flex gap-2">
  <a class="btn btn-primary"
     href="<?= APP_URL ?>/doctor/patient-report/create?patient_id=<?= (int)($r['patient_id'] ?? 0) ?>
       &title=<?= urlencode('Lab Report ('.($r['test_name'] ?? 'Test').')') ?>
       &lab_report_id=<?= (int)$r['id'] ?>">
    Add Patient Report
  </a>
  <a class="btn btn-outline-secondary" href="<?= APP_URL ?>/doctor/lab-reports">Back</a>
</div>

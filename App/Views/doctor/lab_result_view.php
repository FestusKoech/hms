<?php
// views/doctor/lab_result_view.php
// Defensive template: renders even if some fields are missing.

$hasOrder  = is_array($order ?? null)  && !empty($order);
$hasReport = is_array($report ?? null) && !empty($report);
?>

<?php if (!empty($_SESSION['flash'])): ?>
  <div class="alert alert-success small">
    <?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?>
  </div>
<?php endif; ?>

<h1 class="h5 mb-3">Lab Result</h1>

<?php if (!$hasOrder): ?>
  <div class="alert alert-danger">Order not found.</div>
  <?php return; ?>
<?php endif; ?>

<!-- Order header -->
<div class="card mb-3">
  <div class="card-body">
    <div class="mb-1">
      <strong>Patient:</strong>
      <?= htmlspecialchars(trim(($order['first_name'] ?? '').' '.($order['last_name'] ?? ''))) ?>
      <?php if (!empty($order['patient_code'])): ?>
        <span class="text-muted">(<?= htmlspecialchars($order['patient_code']) ?>)</span>
      <?php endif; ?>
    </div>
    <div class="mb-1"><strong>Test:</strong> <?= htmlspecialchars($order['test_name'] ?? '(unknown)') ?></div>
    <div class="mb-1">
      <strong>Status:</strong>
      <span class="badge bg-light text-dark"><?= htmlspecialchars($order['status'] ?? '(unknown)') ?></span>
    </div>
    <div class="text-muted small"><?= htmlspecialchars($order['created_at'] ?? '') ?></div>
  </div>
</div>

<?php if ($hasReport): ?>
  <!-- Latest lab report -->
  <div class="card mb-3">
    <div class="card-header">Latest Lab Report</div>
    <div class="card-body">
      <div class="mb-1">
        <strong>Reported at:</strong>
        <?= htmlspecialchars($report['reported_at'] ?? $report['created_at'] ?? '') ?>
      </div>
      <?php if (!empty($report['technician_name'])): ?>
        <div class="mb-1"><strong>By:</strong> <?= htmlspecialchars($report['technician_name']) ?></div>
      <?php endif; ?>
      <?php if (!empty($report['result_value'])): ?>
        <div class="mb-1"><strong>Result value:</strong> <?= nl2br(htmlspecialchars($report['result_value'])) ?></div>
      <?php endif; ?>
      <?php if (!empty($report['result_text'])): ?>
        <div class="mt-2"><strong>Notes:</strong><br><?= nl2br(htmlspecialchars($report['result_text'])) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <?php
    $patientId   = (int)($order['patient_id'] ?? 0);
    $labReportId = (int)($report['id'] ?? 0);
  ?>
  <div class="d-flex gap-2">
    <?php if ($patientId > 0 && $labReportId > 0): ?>
      <a class="btn btn-success"
         href="<?= APP_URL ?>/doctor/patient-report?patient_id=<?= $patientId ?>&lab_report_id=<?= $labReportId ?>">
        Write Doctor Report
      </a>
    <?php endif; ?>
    <a class="btn btn-outline-secondary" href="<?= APP_URL ?>/doctor/lab-orders">Back to Lab Orders</a>
  </div>

<?php else: ?>
  <div class="alert alert-warning">No lab result posted yet for this order.</div>
  <a class="btn btn-outline-secondary" href="<?= APP_URL ?>/doctor/lab-orders">Back to Lab Orders</a>
<?php endif; ?>

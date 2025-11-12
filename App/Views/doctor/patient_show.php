<!-- Actions: New Lab Order, Write Doctor Report (when coming from a lab report) -->
<div class="d-flex flex-wrap gap-2 mb-3">

  <!-- New Lab Order -->
  <a class="btn btn-sm btn-primary"
     href="<?= APP_URL ?>/doctor/lab-order?patient_id=<?= (int)$p['id'] ?>">
    New Lab Order
  </a>

  <?php
    // Optional context button: when you arrived here from a specific lab report
    // (e.g., you linked patient_show.php with ?lab_report_id=123)
    $ctxLabReportId = (int)($_GET['lab_report_id'] ?? 0);
    if ($ctxLabReportId > 0):
  ?>
    <a class="btn btn-sm btn-success"
       href="<?= APP_URL ?>/doctor/patient-report?patient_id=<?= (int)$p['id'] ?>&lab_report_id=<?= $ctxLabReportId ?>">
      Write Doctor Report
    </a>
  <?php endif; ?>

</div>

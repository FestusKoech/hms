<?php $role = \App\Core\Auth::user()['role'] ?? ''; ?>
<?php if (in_array($role, ['doctor','admin'])): ?>
  <a class="btn btn-sm btn-outline-primary"
     href="<?= APP_URL ?>/doctor/patient-report?patient_id=<?= (int)($r['patient_id'] ?? 0) ?>&lab_report_id=<?= (int)($r['id'] ?? 0) ?>">
     Add Patient Report
  </a>
<?php endif; ?>

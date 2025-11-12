<?php $p = $patient ?? []; $r = $report ?? null; ?>
<?php if(!empty($_SESSION['flash'])): ?>
  <div class="alert alert-info small"><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
<?php endif; ?>

<h1 class="h5 mb-3">Add Patient Report <span class="text-muted">· <?= htmlspecialchars($p['code'] ?? '') ?></span></h1>

<div class="row g-3">
  <div class="col-12 col-lg-8">
    <form class="card" method="post" action="<?= APP_URL ?>/doctor/patient-report" autocomplete="off">
      <div class="card-header">
        <div class="fw-semibold">Clinical Report</div>
        <div class="small text-muted">Write your findings/conclusion for this patient.</div>
      </div>
      <div class="card-body">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
        <input type="hidden" name="patient_id" value="<?= (int)($p['id'] ?? 0) ?>">
        <input type="hidden" name="lab_report_id" value="<?= (int)($r['id'] ?? 0) ?>">

        <?php if($r): ?>
          <div class="alert alert-light border small mb-3">
            <div class="mb-1"><strong>Lab:</strong> <?= htmlspecialchars($r['test_name'] ?? '—') ?></div>
            <div><strong>Value:</strong> <?= htmlspecialchars($r['result_value'] ?? '—') ?> ·
                 <strong>Reported:</strong> <?= htmlspecialchars($r['reported_at'] ?? '—') ?></div>
          </div>
        <?php endif; ?>

        <label class="form-label">Doctor Report</label>
        <textarea class="form-control" name="doctor_report" rows="8"
          placeholder="Assessment, plan, interpretation in context of lab findings..." required></textarea>
      </div>
      <div class="card-footer d-flex justify-content-between">
        <div class="small text-muted">
          Patient: <?= htmlspecialchars(($p['first_name'] ?? '').' '.($p['last_name'] ?? '')) ?>
        </div>
        <div class="d-flex gap-2">
          <a class="btn btn-outline-secondary" href="<?= $r ? (APP_URL.'/lab/report?id='.(int)$r['id']) : (APP_URL.'/doctor/appointments') ?>">Cancel</a>
          <button class="btn btn-primary">Save Report</button>
        </div>
      </div>
    </form>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card">
      <div class="card-header">Patient</div>
      <div class="card-body">
        <div class="fw-semibold">
          <?= htmlspecialchars(($p['first_name'] ?? '').' '.($p['last_name'] ?? '')) ?>
          <span class="text-muted">· <?= htmlspecialchars($p['code'] ?? '') ?></span>
        </div>
      </div>
    </div>
  </div>
</div>

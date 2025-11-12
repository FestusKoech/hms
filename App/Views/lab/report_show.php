<?php
/** @var array $rep */
// $rep keys used: id, test_name, first_name, last_name, code, reported_by_name, reported_at, result_value, result_text
$r = $rep ?? [];
?>
<?php if(!empty($_SESSION['flash'])): ?>
  <div class="alert alert-success small">
    <?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?>
  </div>
<?php endif; ?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h5 mb-0">
    Lab Report
    <span class="text-muted">· #<?= (int)($r['id'] ?? 0) ?></span>
  </h1>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="<?= APP_URL ?>/lab/completed">Completed</a>
    <a class="btn btn-outline-secondary btn-sm" href="<?= APP_URL ?>/lab/search">Search</a>
  </div>
</div>

<div class="row g-3">
  <!-- Left: Report content -->
  <div class="col-12 col-lg-8">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div>
          <div class="fw-semibold"><?= htmlspecialchars($r['test_name'] ?? '—') ?></div>
          <div class="small text-muted">Final results as reported below.</div>
        </div>
        <div class="text-end">
          <div class="small text-muted">Reported by</div>
          <div class="fw-semibold"><?= htmlspecialchars($r['reported_by_name'] ?? '—') ?></div>
          <div class="small text-muted"><?= htmlspecialchars($r['reported_at'] ?? '—') ?></div>
        </div>
      </div>

      <div class="card-body">
        <div class="mb-3">
          <div class="small text-muted">Result Value</div>
          <div class="fs-5 fw-semibold"><?= htmlspecialchars($r['result_value'] ?? '—') ?></div>
        </div>

        <div>
          <div class="small text-muted">Notes / Interpretation</div>
          <div class="border rounded p-3" style="min-height: 96px;">
            <?= nl2br(htmlspecialchars($r['result_text'] ?? '—')) ?>
          </div>
        </div>
      </div>

      <div class="card-footer d-flex align-items-center justify-content-between">
        <div class="small text-muted">
          Test: <?= htmlspecialchars($r['test_name'] ?? '—') ?>
        </div>
        <div class="d-flex gap-2">
          <a class="btn btn-outline-secondary btn-sm" href="<?= APP_URL ?>/lab/completed">Back</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Right: Patient + meta -->
  <div class="col-12 col-lg-4">
    <div class="card">
      <div class="card-header">Patient</div>
      <div class="card-body">
        <div class="mb-2">
          <div class="small text-muted">Name</div>
          <div class="fw-semibold">
            <?= htmlspecialchars(($r['first_name'] ?? '').' '.($r['last_name'] ?? '')) ?>
            <span class="text-muted">· <?= htmlspecialchars($r['code'] ?? '') ?></span>
          </div>
        </div>
        <div class="mb-2">
          <div class="small text-muted">Report ID</div>
          <div>#<?= (int)($r['id'] ?? 0) ?></div>
        </div>
        <div class="mb-2">
          <div class="small text-muted">Status</div>
          <span class="badge bg-success">reported</span>
        </div>
      </div>
      <div class="card-footer d-flex gap-2">
        <a class="btn btn-outline-secondary btn-sm flex-fill" href="<?= APP_URL ?>/lab/search">Search</a>
        <a class="btn btn-outline-secondary btn-sm flex-fill" href="<?= APP_URL ?>/lab/pending">Pending</a>
      </div>
    </div>
  </div>
</div>

<?php
/** @var array $ord */
// $ord keys used: id, test_name, first_name, last_name, code, created_at, status (optional)
$o = $ord ?? [];
?>
<?php if(!empty($_SESSION['flash'])): ?>
  <div class="alert alert-success small">
    <?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?>
  </div>
<?php endif; ?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h5 mb-0">
    Add Results
    <span class="text-muted">· Order #<?= (int)($o['id'] ?? 0) ?></span>
  </h1>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="<?= APP_URL ?>/lab/pending">Back to Pending</a>
    <a class="btn btn-outline-secondary btn-sm" href="<?= APP_URL ?>/lab/search">Search</a>
  </div>
</div>

<div class="row g-3">
  <!-- Left: Form -->
  <div class="col-12 col-lg-8">
    <form class="card" method="post" action="<?= APP_URL ?>/lab/report-save" autocomplete="off">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div>
          <div class="fw-semibold">Result Entry</div>
          <div class="small text-muted">Fill in the measured value and any narrative notes.</div>
        </div>
        <div>
          <?php if (!empty($o['status'])): ?>
            <span class="badge bg-<?= $o['status']==='reported' ? 'success' : 'secondary' ?>">
              <?= htmlspecialchars($o['status']) ?>
            </span>
          <?php else: ?>
            <span class="badge bg-secondary">ordered</span>
          <?php endif; ?>
        </div>
      </div>

      <div class="card-body">
        <input type="hidden" name="order_id" value="<?= (int)($o['id'] ?? 0) ?>">

        <!-- Read-only context fields (styled to match) -->
        <div class="row g-3 mb-1">
          <div class="col-12 col-md-6">
            <label class="form-label">Test</label>
            <input class="form-control" value="<?= htmlspecialchars($o['test_name'] ?? '—') ?>" disabled>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Patient</label>
            <input class="form-control"
                   value="<?= htmlspecialchars(($o['first_name'] ?? '').' '.($o['last_name'] ?? '').' · '.($o['code'] ?? '')) ?>"
                   disabled>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label">Result Value</label>
            <input class="form-control"
                   name="result_value"
                   placeholder="e.g., 5.8 mmol/L"
                   required>
            <div class="form-text">Enter the numeric/qualitative value with unit if applicable.</div>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Result Date</label>
            <input class="form-control"
                   value="<?= htmlspecialchars($o['created_at'] ?? '') ?>"
                   disabled>
          </div>
        </div>

        <div class="mt-3">
          <label class="form-label">Result Notes / Interpretation</label>
          <textarea class="form-control"
                    name="result_text"
                    rows="5"
                    placeholder="Add narrative notes, interpretation, method, flags, etc."
                    ></textarea>
          <div class="form-text">Avoid PII beyond what’s necessary. This note is visible to clinicians.</div>
        </div>
      </div>

      <div class="card-footer d-flex align-items-center justify-content-between">
        <div class="small text-muted">
          Order #<?= (int)($o['id'] ?? 0) ?> · <?= htmlspecialchars($o['test_name'] ?? '—') ?>
        </div>
        <div class="d-flex gap-2">
          <a class="btn btn-outline-secondary" href="<?= APP_URL ?>/lab/pending">Cancel</a>
          <button class="btn btn-primary">Save Report</button>
        </div>
      </div>
    </form>
  </div>

  <!-- Right: Context card -->
  <div class="col-12 col-lg-4">
    <div class="card">
      <div class="card-header">Order Summary</div>
      <div class="card-body">
        <div class="mb-2">
          <div class="small text-muted">Patient</div>
          <div class="fw-semibold">
            <?= htmlspecialchars(($o['first_name'] ?? '').' '.($o['last_name'] ?? '')) ?>
            <span class="text-muted">· <?= htmlspecialchars($o['code'] ?? '') ?></span>
          </div>
        </div>

        <div class="mb-2">
          <div class="small text-muted">Test</div>
          <div><?= htmlspecialchars($o['test_name'] ?? '—') ?></div>
        </div>

        <div class="mb-2">
          <div class="small text-muted">Requested</div>
          <div><?= htmlspecialchars($o['created_at'] ?? '—') ?></div>
        </div>

        <div class="mb-2">
          <div class="small text-muted">Status</div>
          <div>
            <span class="badge bg-<?= ($o['status'] ?? 'ordered') === 'reported' ? 'success' : 'secondary' ?>">
              <?= htmlspecialchars($o['status'] ?? 'ordered') ?>
            </span>
          </div>
        </div>
      </div>
      <div class="card-footer d-flex gap-2">
        <a class="btn btn-outline-secondary btn-sm flex-fill"
           href="<?= APP_URL ?>/lab/pending">Pending Queue</a>
        <a class="btn btn-outline-secondary btn-sm flex-fill"
           href="<?= APP_URL ?>/lab/completed">Completed</a>
      </div>
    </div>
  </div>
</div>

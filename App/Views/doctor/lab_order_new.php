<?php
/** @var array $patient */
/** @var array $tests */
/** @var string $csrf */
$p = $patient ?? [];
?>
<?php if(!empty($_SESSION['flash'])): ?>
  <div class="alert alert-success small">
    <?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?>
  </div>
<?php endif; ?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h5 mb-0">Order Lab Test <span class="text-muted">· <?= htmlspecialchars($p['code'] ?? '') ?></span></h1>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="<?= APP_URL ?>/lab/pending">Lab Pending</a>
    <a class="btn btn-outline-secondary btn-sm" href="<?= APP_URL ?>/lab/completed">Lab Completed</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-8">
    <form class="card" method="post" action="<?= APP_URL ?>/doctor/lab-order" autocomplete="off">
      <div class="card-header">
        <div class="fw-semibold">Order Details</div>
        <div class="small text-muted">Choose the test to request for this patient.</div>
      </div>

      <div class="card-body">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
        <input type="hidden" name="patient_id" value="<?= (int)($p['id'] ?? 0) ?>">

        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label">Patient</label>
            <input class="form-control" value="<?= htmlspecialchars(($p['first_name'] ?? '').' '.($p['last_name'] ?? '')) ?> · <?= htmlspecialchars($p['code'] ?? '') ?>" disabled>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Lab Test</label>
            <select class="form-select" name="test_id" required>
              <option value="">— Select a test —</option>
              <?php foreach ($tests as $t): ?>
                <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <div class="col-12 col-md-6">
  <label class="form-label">Lab Test</label>
  <div class="d-flex gap-2">
    <select class="form-select" name="test_id" required>
      <option value="">— Select a test —</option>
      <?php foreach ($tests as $t): ?>
        <option value="<?= (int)$t['id'] ?>"
          <?php if (!empty($preselect) && (int)$preselect === (int)$t['id']) echo 'selected'; ?>>
          <?= htmlspecialchars($t['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="button" class="btn btn-outline-secondary"
            data-bs-toggle="modal" data-bs-target="#addTestModal">+ New</button>
  </div>
</div>

            
          </div>
        </div>

        <div class="mt-3">
          <label class="form-label">Clinical Notes (optional)</label>
          <textarea class="form-control" name="order_notes" rows="4" placeholder="Reason / context for the test (optional)"></textarea>
          <div class="form-text">Avoid extra PII. Notes help the lab understand the request.</div>
        </div>
      </div>

      <div class="card-footer d-flex align-items-center justify-content-between">
        <div class="small text-muted">Ordered by doctor · will appear in Lab Pending</div>
        <div class="d-flex gap-2">
          <a class="btn btn-outline-secondary" href="<?= APP_URL ?>/doctor">Cancel</a>
          <button class="btn btn-primary">Place Order</button>
        </div>
      </div>
    </form>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card">
      <div class="card-header">Patient Summary</div>
      <div class="card-body">
        <div class="mb-2">
          <div class="small text-muted">Name</div>
          <div class="fw-semibold">
            <?= htmlspecialchars(($p['first_name'] ?? '').' '.($p['last_name'] ?? '')) ?>
            <span class="text-muted">· <?= htmlspecialchars($p['code'] ?? '') ?></span>
          </div>
        </div>
        <div class="mb-2">
          <div class="small text-muted">Patient ID</div>
          <div>#<?= (int)($p['id'] ?? 0) ?></div>
        </div>
      </div>
      <div class="card-footer">
        <a class="btn btn-outline-secondary btn-sm w-100" href="<?= APP_URL ?>/lab/pending">Go to Lab Pending</a>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="addTestModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="<?= APP_URL ?>/lab/test/create" autocomplete="off">
      <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
      <input type="hidden" name="patient_id" value="<?= (int)($p['id'] ?? 0) ?>">

      <div class="modal-header">
        <h5 class="modal-title">Add New Lab Test</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Test name</label>
          <input class="form-control" name="name" placeholder="e.g., Widal, Malaria RDT, FBC" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Infection / Category (optional)</label>
          <input class="form-control" name="infection" placeholder="e.g., Typhoid, Malaria, Hematology">
          <div class="form-text">Optional; leave blank if not applicable.</div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary">Save Test</button>
      </div>
    </form>
  </div>
</div>

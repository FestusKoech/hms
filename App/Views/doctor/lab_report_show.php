<?php
// views/lab/report.php (or whatever file your /lab/report renders)
$report = $report ?? ($r ?? null); // tolerate $report or $r
$patient = $patient ?? ($p ?? null);
$order   = $order ?? null;

$pid = (int)($patient['id'] ?? $report['patient_id'] ?? $order['patient_id'] ?? 0);
$code = $patient['code'] ?? ('P'.str_pad((string)$pid, 3, '0', STR_PAD_LEFT));
$fullName = trim(($patient['first_name'] ?? '').' '.($patient['last_name'] ?? ''));

// routes (adjust if yours differ)
$URL_BASE     = APP_URL ?? '';
$URL_PENDING  = $URL_BASE . '/lab/pending';
$URL_DONE     = $URL_BASE . '/lab/completed';
$URL_SEARCH   = $URL_BASE . '/lab/search';       // if you have a search route
$URL_PATIENT  = $URL_BASE . '/doctor/patient?id='.$pid; // Doctor's patient view
$URL_NEW_ORDER= $URL_BASE . '/doctor/lab-order?patient_id='.$pid;
$URL_BACK     = $URL_BASE . '/lab/pending';
?>

<?php if (!empty($_SESSION['flash'])): ?>
  <div class="alert alert-success small mb-2">
    <?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?>
  </div>
<?php endif; ?>

<!-- Sticky subnav / toolbar -->
<!-- Simple page header (non-sticky, no duplicate nav) -->
<div class="d-flex align-items-center justify-content-between mb-3">
  <div class="d-flex align-items-center gap-2">
    <a class="btn btn-sm btn-outline-secondary" href="<?= $URL_BACK ?>">← Back</a>
    <h1 class="h5 mb-0">Lab Report <span class="text-muted">· #<?= (int)($report['id'] ?? 0) ?></span></h1>
  </div>
  <?php if(!empty($pid)): ?>
    <a class="btn btn-sm btn-outline-primary" href="<?= $URL_PATIENT ?>">Patient: <?= htmlspecialchars($code) ?></a>
  <?php endif; ?>
</div>


<div class="row g-3">
  <!-- LEFT: Patient panel -->
  <div class="col-12 col-lg-4">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div class="fw-semibold">Patient</div>
        <?php if ($pid > 0): ?>
          <a href="<?= $URL_PATIENT ?>" class="btn btn-xs btn-outline-secondary">Open Profile</a>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="me-2 rounded-circle bg-light border" style="width:40px;height:40px;"></div>
          <div>
            <div class="fw-semibold"><?= htmlspecialchars($fullName ?: 'Unknown Patient') ?></div>
            <div class="text-muted small"><?= htmlspecialchars($code) ?></div>
          </div>
        </div>

        <div class="small text-muted mb-2">
          <!-- Show a few structured details if available -->
          <?php if (!empty($patient['national_id'])): ?>
            <div><span class="text-secondary">ID:</span> <?= htmlspecialchars($patient['national_id']) ?></div>
          <?php endif; ?>
          <?php if (!empty($patient['phone'])): ?>
            <div><span class="text-secondary">Phone:</span> <?= htmlspecialchars($patient['phone']) ?></div>
          <?php endif; ?>
          <?php if (!empty($patient['dob'])): ?>
            <div><span class="text-secondary">DOB:</span> <?= htmlspecialchars($patient['dob']) ?></div>
          <?php endif; ?>
        </div>

        <!-- Quick links (inside the patient card) -->
        <div class="d-flex flex-wrap gap-2 mt-3">
          <!-- <a class="btn btn-sm btn-outline-primary" href="<?= $URL_PENDING ?>">Pending</a>
          <a class="btn btn-sm btn-outline-primary" href="<?= $URL_DONE ?>">Completed</a> -->
          <?php if ($pid > 0): ?>
            <a class="btn btn-sm btn-outline-success" href="<?= $URL_NEW_ORDER ?>">New Lab Order</a>
          <?php endif; ?>
        </div>

        <!-- Quick search (in-card) -->
        <form class="d-flex mt-3" method="get" action="<?= $URL_SEARCH ?>" onsubmit="return this.q.value.trim().length>0;">
          <input type="text" name="q" class="form-control form-control-sm" placeholder="Search…">
          <button class="btn btn-sm btn-primary ms-2">Go</button>
        </form>
      </div>
    </div>
  </div>

  <!-- RIGHT: Report panel -->
  <div class="col-12 col-lg-8">
    <div class="card shadow-sm">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <div class="fw-semibold">Lab Report</div>
          <div class="text-muted small">
            <?php if (!empty($report['reported_at'])): ?>
              Reported: <?= htmlspecialchars($report['reported_at']) ?>
            <?php elseif (!empty($report['created_at'])): ?>
              Created: <?= htmlspecialchars($report['created_at']) ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="card-body">
        <?php if ($report): ?>
          <?php if (!empty($report['test_name'])): ?>
            <div class="mb-2"><span class="text-secondary">Test:</span> <?= htmlspecialchars($report['test_name']) ?></div>
          <?php elseif (!empty($order['test_name'])): ?>
            <div class="mb-2"><span class="text-secondary">Test:</span> <?= htmlspecialchars($order['test_name']) ?></div>
          <?php endif; ?>

          <?php if (!empty($report['result_value'])): ?>
            <div class="mb-2"><span class="text-secondary">Result Value:</span>
              <div class="fw-semibold"><?= nl2br(htmlspecialchars($report['result_value'])) ?></div>
            </div>
          <?php endif; ?>

          <?php if (!empty($report['result_text'])): ?>
            <div class="mb-2"><span class="text-secondary">Notes:</span>
              <div><?= nl2br(htmlspecialchars($report['result_text'])) ?></div>
            </div>
          <?php endif; ?>

          <div class="d-flex flex-wrap gap-2 mt-3">
            <?php if (!empty($pid) && !empty($report['id'])): ?>
              <a class="btn btn-success"
                 href="<?= $URL_BASE ?>/doctor/patient-report?patient_id=<?= (int)$pid ?>&lab_report_id=<?= (int)$report['id'] ?>">
                Write Doctor Report
              </a>
            <?php endif; ?>
            <?php if ($pid > 0): ?>
              <!-- <a class="btn btn-outline-success" href="<?= $URL_NEW_ORDER ?>">New Lab Order</a> -->
            <?php endif; ?>
            <!-- <a class="btn btn-outline-secondary" href="<?= $URL_PENDING ?>">Back to Pending</a> -->
          </div>

        <?php else: ?>
          <div class="alert alert-warning">No lab report content is available for this record.</div>
          <?php if ($pid > 0): ?>
            <a class="btn btn-outline-success" href="<?= $URL_NEW_ORDER ?>">Create New Lab Order</a>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<style>
  /* Tiny polish */
  .btn-xs { padding: .15rem .45rem; font-size: .75rem; }
</style>

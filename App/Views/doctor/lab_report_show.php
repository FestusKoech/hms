<h1 class="h4 mb-3">Lab Report #<?= (int)$r['id'] ?></h1>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header"><strong>Patient / Test</strong></div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-4">Patient</dt><dd class="col-8"><?= htmlspecialchars($r['first_name'].' '.$r['last_name']) ?></dd>
          <dt class="col-4">Test</dt><dd class="col-8"><?= htmlspecialchars($r['test_name']) ?></dd>
          <dt class="col-4">Ordered by</dt><dd class="col-8"><?= htmlspecialchars($r['ordered_by_name']) ?></dd>
          <dt class="col-4">Reported by</dt><dd class="col-8"><?= htmlspecialchars($r['reported_by_name'] ?? '-') ?></dd>
          <dt class="col-4">Reported at</dt><dd class="col-8"><?= htmlspecialchars($r['reported_at'] ?? '-') ?></dd>
        </dl>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header"><strong>Result</strong></div>
      <div class="card-body">
        <p class="mb-1"><strong>Value:</strong> <?= htmlspecialchars($r['result_value'] ?? '-') ?></p>
        <p class="mb-0"><strong>Text:</strong><br><?= htmlspecialchars($r['result_text'] ?? '-') ?></p>
      </div>
    </div>
  </div>
</div>

<div class="mt-3">
  <a class="btn btn-outline-light" href="<?= APP_URL ?>/doctor/lab-reports">Back</a>
</div>

<h1 class="h4 mb-3">Lab · Search Patient</h1>

<form class="card card-body mb-3" method="get" action="<?= APP_URL ?>/lab/search">
  <div class="row g-2 align-items-center">
    <div class="col">
      <input class="form-control" name="q" value="<?= htmlspecialchars($q ?? '') ?>"
             placeholder="Search by name, code, or ID…" autofocus>
    </div>
    <div class="col-auto"><button class="btn btn-primary">Search</button></div>
    <div class="col-auto">
      <a class="btn btn-outline-secondary" href="<?= APP_URL ?>/lab/pending">Pending</a>
      <a class="btn btn-outline-secondary" href="<?= APP_URL ?>/lab/completed">Completed</a>
    </div>
  </div>
</form>

<?php if (!empty($q)): ?>
  <div class="card">
    <div class="card-header">Results</div>
    <div class="list-group list-group-flush">
      <?php if (!empty($results)): ?>
        <?php foreach ($results as $r): ?>
          <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
             href="<?= APP_URL ?>/lab/patient?id=<?= (int)$r['id'] ?>">
            <div>
              <div class="fw-semibold">
                <?= htmlspecialchars(($r['first_name']??'').' '.($r['last_name']??'')) ?>
              </div>
              <div class="small text-muted"><?= htmlspecialchars($r['code'] ?? '—') ?></div>
            </div>
            <span class="btn btn-sm btn-outline-secondary">Open</span>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="list-group-item text-muted">No matches.</div>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

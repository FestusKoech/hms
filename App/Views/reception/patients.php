<h1 class="h4 mb-3">Reception · Patients</h1>

<!-- Search -->
<form class="card card-body mb-3" method="get" action="<?= APP_URL ?>/reception/patients">
  <div class="row g-2 align-items-center">
    <div class="col">
      <input type="text"
             name="q"
             value="<?= htmlspecialchars($q ?? '') ?>"
             class="form-control"
             placeholder="Search by name, code, ID, or phone…"
             autofocus>
    </div>
    <div class="col-auto">
      <button class="btn btn-primary" type="submit">Search</button>
    </div>
    <div class="col-auto">
      <!-- Register is always available -->
      <a class="btn btn-outline-primary" href="<?= APP_URL ?>/reception/patients/create">
        Register Patient
      </a>
    </div>
  </div>
</form>

<!-- Results (only after a query) -->
<?php if (!empty($q)): ?>
  <div class="card mb-3">
    <div class="card-header">Results for “<?= htmlspecialchars($q) ?>”</div>
    <div class="list-group list-group-flush">
      <?php if (!empty($results)): ?>
        <?php foreach ($results as $r): ?>
          <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
             href="<?= APP_URL ?>/reception/patients?patient_id=<?= (int)$r['id'] ?>&q=<?= urlencode($q) ?>">
            <div>
              <div class="fw-semibold">
                <?= htmlspecialchars(($r['first_name'] ?? '').' '.($r['last_name'] ?? '')) ?>
              </div>
              <div class="small text-muted">
                <?= htmlspecialchars(($r['code'] ?? '—').' · '.($r['phone'] ?? '—')) ?>
              </div>
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

<!-- Selected patient context + Quick Links (only after selection) -->
<?php if (!empty($patient)): ?>
  <div class="card">
    <div class="card-header">Selected Patient</div>
    <div class="card-body d-flex justify-content-between align-items-center">
      <div>
        <div class="fw-semibold">
          <?= htmlspecialchars(($patient['first_name'] ?? '').' '.($patient['last_name'] ?? '')) ?>
        </div>
       <div class="small text-muted">
  <?= htmlspecialchars($r['code'] ?? '—') ?>
</div>

      </div>
      <div class="d-flex gap-2">
        <a class="btn btn-primary"
           href="<?= APP_URL ?>/reception/appointments/create?patient_id=<?= (int)$patient['id'] ?>">
          Schedule Appointment
        </a>
        <a class="btn btn-outline-primary"
           href="<?= APP_URL ?>/reception/appointments?patient_id=<?= (int)$patient['id'] ?>">
          View Appointments
        </a>
      </div>
    </div>
  </div>
<?php endif; ?>

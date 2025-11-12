<?php if (!empty($_SESSION['flash'])): ?>
  <div class="alert alert-info small"><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
<?php endif; ?>

<h1 class="h5 mb-3">Reception · Patients</h1>

<form class="card card-body mb-3" method="get" action="<?= APP_URL ?>/reception/patients">
  <div class="row g-2 align-items-center">
    <div class="col">
      <input type="text" class="form-control" name="q" value="<?= htmlspecialchars($q ?? '') ?>"
             placeholder="Search by patient no, name, or ID…" autofocus>
    </div>
    <div class="col-auto">
      <button class="btn btn-primary">Search</button>
    </div>
  </div>
</form>

<div class="card">
  <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead>
        <tr>
          <th>#</th><th>Patient No</th><th>Name</th><th>DOB</th><th>Sex</th><th>Contact</th><th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach (($results ?? []) as $r): ?>
        <?php
          $id    = (int)$r['id'];
          $name  = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
          $dob   = (string)($r['dob'] ?? '');
          $sex   = (string)($r['sex'] ?? '');
          $phone = (string)($r['contact'] ?? '');
        ?>
        <tr>
          <td><?= $id ?></td>
          <td><?= htmlspecialchars((string)($r['patient_no'] ?? '')) ?></td>
          <td><?= htmlspecialchars($name) ?></td>
          <td><?= htmlspecialchars($dob) ?></td>
          <td><?= htmlspecialchars($sex) ?></td>
          <td><?= htmlspecialchars($phone) ?></td>
          <td class="text-end">
            <button
              type="button"
              class="btn btn-sm btn-outline-primary"
              data-bs-toggle="modal"
              data-bs-target="#quickScheduleModal"
              data-patient-id="<?= $id ?>"
              data-patient-name="<?= htmlspecialchars($name) ?>"
            >Add Schedule</button>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Quick Schedule Modal -->
<div class="modal fade" id="quickScheduleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="<?= APP_URL ?>/reception/appointments/quick">
      <div class="modal-header">
        <h5 class="modal-title">Schedule Appointment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="patient_id" id="qs_patient_id">

        <div class="mb-2 small text-muted">
          Patient: <span id="qs_patient_name" class="fw-semibold"></span>
        </div>

        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Start</label>
            <input type="datetime-local" name="starts_at" class="form-control" required>
          </div>
          <div class="col-12">
            <label class="form-label">End (optional)</label>
            <input type="datetime-local" name="ends_at" class="form-control">
          </div>
          <div class="col-12">
            <label class="form-label">Reason (optional)</label>
            <input type="text" name="reason" class="form-control" maxlength="255" placeholder="e.g., Follow-up">
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-success" type="submit">Save</button>
        <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('show.bs.modal', function(e){
  const modal = e.target;
  if (modal.id !== 'quickScheduleModal') return;
  const btn = e.relatedTarget; if (!btn) return;

  modal.querySelector('#qs_patient_id').value = btn.getAttribute('data-patient-id');
  modal.querySelector('#qs_patient_name').textContent = btn.getAttribute('data-patient-name');
});
</script>

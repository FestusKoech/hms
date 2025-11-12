<?php if(!empty($_SESSION['flash'])): ?>
  <div class="alert alert-success small"><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
<?php endif; ?>

<h1 class="h5 mb-3">Prescriptions (All)</h1>
<div class="card mb-4">
  <div class="table-responsive">
    <table class="table table-striped mb-0">
      <thead><tr><th>Patient</th><th>Drug</th><th>Days</th><th>Status</th><th class="text-end">Action</th></tr></thead>
      <tbody>
      <?php foreach($items as $i): ?>
        <tr>
          <td><?= htmlspecialchars($i['first_name'].' '.$i['last_name']) ?></td>
          <td><?= htmlspecialchars($i['drug']) ?></td>
          <td><?= (int)$i['duration_days'] ?></td>
          <td>
            <?php if((int)$i['dispensed']===1): ?>
              <span class="badge text-bg-success">Dispensed</span>
            <?php else: ?>
              <span class="badge text-bg-warning">Pending</span>
            <?php endif; ?>
          </td>
         <td class="text-end">
  <?php if (($i['status'] ?? '') !== 'dispensed'): ?>
    <button
      type="button"
      class="btn btn-sm btn-primary js-dispense"
      data-url="<?= APP_URL ?>/pharmacy/dispense"
      data-token="<?= htmlspecialchars($csrf) ?>"
      data-prescription-id="<?= (int)($i['prescription_id'] ?? 0) ?>"
      data-patient-id="<?= (int)($i['patient_id'] ?? 0) ?>"
    >
      Mark Dispensed
    </button>
  <?php else: ?>
    <span class="badge bg-success">Dispensed</span>
  <?php endif; ?>
</td>

        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<h2 class="h6 mb-2">Recent Dispense History</h2>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>#</th><th>Patient</th><th>Drug</th><th>Qty</th><th>When</th></tr></thead>
      <tbody>
      <?php foreach(($history ?? []) as $h): ?>
        <tr>
          <td><?= (int)$h['id'] ?></td>
          <td><?= htmlspecialchars($h['first_name'].' '.$h['last_name']) ?></td>
          <td><?= htmlspecialchars($h['drug']) ?></td>
          <td><?= (int)$h['qty'] ?></td>
          <td class="text-muted"><?= htmlspecialchars($h['dispensed_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      <script>
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('.js-dispense');
  if (!btn) return;

  e.preventDefault();

  if (!window.confirm('Check out?')) return;

  const fd = new FormData();
  fd.append('_token', btn.dataset.token);
  fd.append('prescription_id', btn.dataset.prescriptionId);
  fd.append('patient_id', btn.dataset.patientId);

  try {
    const res = await fetch(btn.dataset.url, {
      method: 'POST',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    let data = null;
    try { data = await res.json(); } catch (_) {}

    if (res.ok && (!data || !data.error)) {
      location.reload();
    } else {
      alert((data && data.error) || 'Failed to dispense.');
    }
  } catch (err) {
    alert('Network error: ' + err.message);
  }
});
</script>

      </tbody>
    </table>
  </div>
</div>


<?php if (!empty($_SESSION['checkout_patient_id'])): ?>
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Checkout patient?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Drugs have been dispensed. Mark the patient’s appointment as <strong>completed</strong>?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">No</button>
        <form method="post" action="<?= APP_URL ?>/appointments/complete" class="d-inline">
          <input type="hidden" name="_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
          <input type="hidden" name="patient_id" value="<?= (int)$_SESSION['checkout_patient_id'] ?>">
          <input type="hidden" name="_back" value="<?= htmlspecialchars($_SESSION['checkout_back'] ?? (APP_URL.'/pharmacy/fulfill')) ?>">
          <button class="btn btn-success">Yes, complete</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const m = new bootstrap.Modal(document.getElementById('checkoutModal'));
  m.show();
});
</script>
<?php
unset($_SESSION['checkout_patient_id'], $_SESSION['checkout_back']);
endif;
?>


<?php if (!empty($_SESSION['checkout_patient_id'])): ?>
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Checkout patient?</h5>
        <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Drugs have been dispensed. Mark the patient’s appointment as <strong>completed</strong>?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">No</button>
        <form method="post" action="<?= APP_URL ?>/appointments/complete" class="d-inline">
          <input type="hidden" name="_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
          <input type="hidden" name="patient_id" value="<?= (int)$_SESSION['checkout_patient_id'] ?>">
          <input type="hidden" name="_back" value="<?= htmlspecialchars($_SESSION['checkout_back'] ?? (APP_URL.'/pharmacy/fulfill')) ?>">
          <button class="btn btn-success">Yes, complete</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const m = new bootstrap.Modal(document.getElementById('checkoutModal'));
  m.show();
});
</script>
<?php unset($_SESSION['checkout_patient_id'], $_SESSION['checkout_back']); endif; ?>

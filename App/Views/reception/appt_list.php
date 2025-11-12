<?php
// Reception: Patient-scoped appointment list (no reason visible)
?>
<h1 class="h4 mb-3">Appointments</h1>

<?php if (empty($patient_id)): ?>
  <div class="alert alert-warning">
    Open a patient first from <a href="<?= APP_URL ?>/reception/patients">Reception → Patients</a>.
  </div>
<?php else: ?>
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div class="small text-muted">For Patient #<?= (int)$patient_id ?></div>
    <div class="d-flex gap-2">
      <a class="btn btn-primary btn-sm" href="<?= APP_URL ?>/reception/appointments/create?patient_id=<?= (int)$patient_id ?>">
        Schedule Appointment
      </a>
      <form method="post" action="<?= APP_URL ?>/reception/queue" class="d-inline">
  <input type="hidden" name="_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
  <input type="hidden" name="patient_id" value="<?= (int)$patient_id ?>">
  <button class="btn btn-outline-primary btn-sm">Add to Doctor Queue</button>
</form>

      <a class="btn btn-outline-secondary btn-sm" href="<?= APP_URL ?>/reception/patients?patient_id=<?= (int)$patient_id ?>">
        Back
      </a>
    </div>
  </div>

  <div class="card">
    <table class="table mb-0">
      <thead>
  <tr>
    <th>Starts</th>
    <th>Ends</th>
    <th>Doctor</th>
    <th>Status</th>
    <th class="text-end">Actions</th>
  </tr>
</thead>
<tbody>
  <?php if (!empty($rows)): ?>
    <?php foreach ($rows as $a): ?>
      <tr>
        <td><?= htmlspecialchars($a['starts_at']) ?></td>
        <td><?= htmlspecialchars($a['ends_at']) ?></td>
        <td><?= htmlspecialchars($a['doctor']) ?></td>
        <td>
          <span class="badge bg-<?= ($a['status']==='scheduled'?'secondary':($a['status']==='completed'?'success':'danger')) ?>">
            <?= htmlspecialchars($a['status']) ?>
          </span>
        </td>
        <td class="text-end">
          <a class="btn btn-sm btn-outline-primary"
             href="<?= APP_URL ?>/reception/appointments/edit?id=<?= (int)$a['id'] ?>">Edit</a>
          <a class="btn btn-sm btn-outline-secondary"
             target="_blank"
             href="<?= APP_URL ?>/reception/appointments/slip?id=<?= (int)$a['id'] ?>">Print</a>
          <?php if ($a['status']!=='cancelled'): ?>
            <form method="post" action="<?= APP_URL ?>/reception/appointments/cancel" class="d-inline"
                  onsubmit="return confirm('Cancel this appointment?');">
              <input type="hidden" name="_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
              <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
              <input type="hidden" name="patient_id" value="<?= (int)$patient_id ?>">
              <button class="btn btn-sm btn-outline-danger">Cancel</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr><td colspan="5" class="text-muted">No appointments yet.</td></tr>
  <?php endif; ?>
</tbody>

    </table>
  </div>
<?php endif; ?>

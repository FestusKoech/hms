<?php /** @var array $appt */ ?>
<h1 class="h4 mb-3">Edit Appointment</h1>

<form class="card card-body" method="post" action="<?= APP_URL ?>/reception/appointments/update">
  <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
  <input type="hidden" name="id" value="<?= (int)$appt['id'] ?>">
  <input type="hidden" name="patient_id" value="<?= (int)$appt['patient_id'] ?>">

  <div class="mb-3">
    <label class="form-label">Doctor</label>
    <select name="doctor_id" class="form-select" required>
      <?php foreach ($doctors as $d): ?>
        <option value="<?= (int)$d['id'] ?>" <?= ($d['id']==$appt['doctor_id']?'selected':'') ?>>
          <?= htmlspecialchars($d['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="row g-2">
    <div class="col">
      <label class="form-label">Starts</label>
      <input type="datetime-local" name="starts_at" class="form-control"
             value="<?= htmlspecialchars(str_replace(' ', 'T', substr($appt['starts_at'],0,16))) ?>" required>
    </div>
    <div class="col">
      <label class="form-label">Ends</label>
      <input type="datetime-local" name="ends_at" class="form-control"
             value="<?= htmlspecialchars(str_replace(' ', 'T', substr($appt['ends_at'],0,16))) ?>" required>
    </div>
  </div>

  <div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary" type="submit">Save Changes</button>
    <a class="btn btn-outline-secondary"
       href="<?= APP_URL ?>/reception/appointments?patient_id=<?= (int)$appt['patient_id'] ?>">Back</a>
  </div>
</form>

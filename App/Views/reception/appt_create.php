<?php
// File: App/Views/reception/appt_create.php
?>
<h1 class="h4 mb-3">Schedule Appointment</h1>

<form class="card card-body" method="post" action="<?= APP_URL ?>/reception/appointments/store">
  <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
  <input type="hidden" name="patient_id" value="<?= (int)$patient_id ?>">

  <div class="mb-3">
    <label class="form-label">Doctor</label>
    <select name="doctor_id" class="form-select" required>
      <?php
      // Load available doctors
      $docs = \App\Core\DB::pdo()
        ->query("SELECT id, name FROM users WHERE role='doctor' ORDER BY name")
        ->fetchAll();
      foreach ($docs as $d): ?>
        <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="row g-2">
    <div class="col">
      <label class="form-label">Starts</label>
      <input type="datetime-local" name="starts_at" class="form-control" required>
    </div>
    <div class="col">
      <label class="form-label">Ends</label>
      <input type="datetime-local" name="ends_at" class="form-control" required>
    </div>
  </div>

  <div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary" type="submit">Save</button>
    <a class="btn btn-outline-secondary" href="<?= APP_URL ?>/reception/patients?patient_id=<?= (int)$patient_id ?>">Back</a>
  </div>
</form>

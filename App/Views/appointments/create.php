<h1 class="h4 mb-3">Schedule Appointment</h1>
<form method="post" action="<?= APP_URL ?>/appointments/store" class="card card-body">
  <input type="hidden" name="_token" value="<?= $csrf ?>">
  <div class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Patient</label>
      <select class="form-select" name="patient_id" required>
        <?php foreach($patients as $p): ?>
          <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['first_name'].' '.$p['last_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Doctor</label>
      <select class="form-select" name="doctor_id" required>
        <?php foreach($doctors as $d): ?>
          <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2"><label class="form-label">Start</label><input class="form-control" type="datetime-local" name="starts_at" required></div>
    <div class="col-md-2"><label class="form-label">End</label><input class="form-control" type="datetime-local" name="ends_at" required></div>
    <div class="col-12"><label class="form-label">Reason</label><input class="form-control" name="reason"></div>
  </div>
  <div class="mt-3"><button class="btn btn-primary">Save</button></div>
</form>

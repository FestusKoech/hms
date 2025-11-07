<h1 class="h4 mb-3">New Patient</h1>
<form method="post" action="<?= APP_URL ?>/patients/store" class="card card-body">
  <input type="hidden" name="_token" value="<?= $csrf ?>">
  <div class="row g-3">
    <div class="col-md-3"><label class="form-label">Patient No</label><input name="patient_no" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label">First name</label><input name="first_name" class="form-control" required></div>
    <div class="col-md-5"><label class="form-label">Last name</label><input name="last_name" class="form-control" required></div>
    <div class="col-md-3"><label class="form-label">DOB</label><input type="date" name="dob" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Sex</label>
      <select name="sex" class="form-select"><option value="">-</option><option>M</option><option>F</option><option>O</option></select>
    </div>
    <div class="col-md-3"><label class="form-label">Contact</label><input name="contact" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Emergency Contact</label><input name="emergency_contact" class="form-control"></div>
    <div class="col-12"><label class="form-label">Address</label><input name="address" class="form-control"></div>
  </div>
  <div class="mt-3"><button class="btn btn-primary">Save</button></div>
</form>

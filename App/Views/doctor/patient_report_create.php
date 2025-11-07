<h1 class="h4 mb-3">Patient Report</h1>
<form method="post" action="<?= APP_URL ?>/doctor/patient-report" class="card card-body">
  <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
  <input type="hidden" name="patient_id" value="<?= (int)$patient_id ?>">
  <div class="mb-3"><label class="form-label">Title</label><input class="form-control" name="title" required></div>
  <div class="mb-3"><label class="form-label">Report</label><textarea class="form-control" rows="7" name="body" required></textarea></div>
  <button class="btn btn-primary">Save Report</button>
</form>

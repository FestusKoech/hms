<?php use App\Core\Helpers; $errs = Helpers::errors(); ?>
<h1 class="h5 mb-3">Create Appointment (No Doctor Required)</h1>

<form method="post" action="<?= APP_URL ?>/reception/appointments">
  <input type="hidden" name="_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

  <div class="mb-3">
    <label class="form-label">Patient ID</label>
    <input type="number" name="patient_id" class="form-control" value="<?= Helpers::old('patient_id') ?>">
    <?php if(!empty($errs['patient_id'])): ?>
      <div class="text-danger small"><?= htmlspecialchars($errs['patient_id']) ?></div>
    <?php endif; ?>
  </div>

  <div class="mb-3">
    <label class="form-label">Starts At</label>
    <input type="datetime-local" name="starts_at" class="form-control" value="<?= Helpers::old('starts_at') ?>">
    <?php if(!empty($errs['starts_at'])): ?>
      <div class="text-danger small"><?= htmlspecialchars($errs['starts_at']) ?></div>
    <?php endif; ?>
  </div>

  <div class="mb-3">
    <label class="form-label">Ends At</label>
    <input type="datetime-local" name="ends_at" class="form-control" value="<?= Helpers::old('ends_at') ?>">
    <?php if(!empty($errs['ends_at'])): ?>
      <div class="text-danger small"><?= htmlspecialchars($errs['ends_at']) ?></div>
    <?php endif; ?>
  </div>

  <div class="mb-3">
    <label class="form-label">Reason (optional)</label>
    <input type="text" name="reason" class="form-control" value="<?= Helpers::old('reason') ?>">
  </div>

  <button class="btn btn-primary">Save Appointment</button>
</form>

<?php Helpers::clearErrors(); Helpers::clearOld(); ?>

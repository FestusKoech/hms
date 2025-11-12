<?php if (!empty($_SESSION['flash'])): ?>
  <div class="alert alert-info small"><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
<?php endif; ?>

<h1 class="h5 mb-3">Doctor · Scheduled Patients</h1>

<!-- Unassigned scheduled -->
<div class="card mb-4">
  <div class="card-header">Waiting (Unassigned)</div>
  <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead>
        <tr>
          <th>When</th><th>Patient No</th><th>Name</th><th>Sex</th><th>Contact</th><th>Reason</th><th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach (($unassigned ?? []) as $a): ?>
        <?php
          $id    = (int)$a['id'];
          $name  = trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''));
          $when  = htmlspecialchars((string)$a['starts_at']);
          $pno   = htmlspecialchars((string)($a['patient_no'] ?? ''));
          $sex   = htmlspecialchars((string)($a['sex'] ?? ''));
          $ct    = htmlspecialchars((string)($a['contact'] ?? ''));
          $rsn   = htmlspecialchars((string)($a['reason'] ?? ''));
        ?>
        <tr>
          <td><?= $when ?></td>
          <td><?= $pno ?></td>
          <td><?= htmlspecialchars($name) ?></td>
          <td><?= $sex ?></td>
          <td><?= $ct ?></td>
          <td><?= $rsn ?></td>
          <td class="text-end">
            <form method="post" action="<?= APP_URL ?>/doctor/claim" class="d-inline" onsubmit="return confirm('Start this consultation?');">
              <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="appointment_id" value="<?= $id ?>">
              <button class="btn btn-sm btn-primary">Start</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($unassigned)): ?>
        <tr><td colspan="7" class="text-muted small">No unassigned scheduled patients.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- My in-progress -->
<div class="card">
  <div class="card-header">My In-Progress</div>
  <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead>
        <tr>
          <th>When</th><th>Patient No</th><th>Name</th><th>Reason</th><th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach (($mine ?? []) as $a): ?>
        <?php
          $id    = (int)$a['id'];
          $name  = trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''));
          $when  = htmlspecialchars((string)$a['starts_at']);
          $pno   = htmlspecialchars((string)($a['patient_no'] ?? ''));
          $rsn   = htmlspecialchars((string)($a['reason'] ?? ''));
        ?>
        <tr>
          <td><?= $when ?></td>
          <td><?= $pno ?></td>
          <td><?= htmlspecialchars($name) ?></td>
          <td><?= $rsn ?></td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="<?= APP_URL ?>/doctor/visit?appointment_id=<?= $id ?>">Open</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($mine)): ?>
        <tr><td colspan="5" class="text-muted small">You have no in-progress consultations.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

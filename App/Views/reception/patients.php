<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4">Reception · Patients</h1>
  <a class="btn btn-primary" href="<?= APP_URL ?>/reception/patients/create">Register</a>
</div>
<?php $data = $data ?? []; ?>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>#</th><th>Patient No</th><th>Name</th><th>Contact</th></tr></thead>
      <tbody>
      <?php foreach(($data ?? []) as $p): ?>
        <tr>
          <td><?= $p['id'] ?></td>
          <td><?= htmlspecialchars($p['patient_no']) ?></td>
          <td><?= htmlspecialchars($p['first_name'].' '.$p['last_name']) ?></td>
          <td><?= htmlspecialchars($p['contact'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4">Patients</h1>
  <a class="btn btn-primary" href="<?= APP_URL ?>/patients/create">New Patient</a>
</div>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>#</th><th>Patient No</th><th>Name</th><th>Contact</th><th></th></tr></thead>
      <tbody>
      <?php foreach($data as $p): ?>
        <tr>
          <td><?= $p['id'] ?></td>
          <td><?= htmlspecialchars($p['patient_no']) ?></td>
          <td><?= htmlspecialchars($p['first_name'].' '.$p['last_name']) ?></td>
          <td><?= htmlspecialchars($p['contact'] ?? '') ?></td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-secondary" href="<?= APP_URL ?>/patients/show?id=<?= $p['id'] ?>">View</a>
            <a class="btn btn-sm btn-outline-primary" href="<?= APP_URL ?>/patients/edit?id=<?= $p['id'] ?>">Edit</a>
            <form class="d-inline" method="post" action="<?= APP_URL ?>/patients/delete" onsubmit="return confirm('Delete?')">
              <input type="hidden" name="_token" value="<?= \App\Core\Csrf::token() ?>">
              <input type="hidden" name="id" value="<?= $p['id'] ?>">
              <button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

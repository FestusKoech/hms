<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4">Appointments</h1>
  <a class="btn btn-primary" href="<?= APP_URL ?>/appointments/create">Schedule</a>
</div>
<div class="card">
  <div class="table-responsive">
    <table class="table table-striped mb-0">
      <thead><tr><th>Patient</th><th>Doctor</th><th>Starts</th><th>Ends</th><th>Reason</th><th></th></tr></thead>
      <tbody>
      <?php foreach($items as $a): ?>
        <tr>
          <td><?= htmlspecialchars($a['first_name'].' '.$a['last_name']) ?></td>
          <td><?= htmlspecialchars($a['doctor']) ?></td>
          <td><?= $a['starts_at'] ?></td>
          <td><?= $a['ends_at'] ?></td>
          <td><?= htmlspecialchars($a['reason'] ?? '') ?></td>
          <td class="text-end">
            <form method="post" action="<?= APP_URL ?>/appointments/delete" onsubmit="return confirm('Cancel appointment?')">
              <input type="hidden" name="_token" value="<?= \App\Core\Csrf::token() ?>">
              <input type="hidden" name="id" value="<?= $a['id'] ?>">
              <button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<h1 class="h5 mb-3">Scheduled Patients (Unclaimed)</h1>
<table class="table table-sm align-middle">
  <thead><tr><th>Time</th><th>Patient</th><th>Reason</th><th></th></tr></thead>
  <tbody>
  <?php foreach (($items ?? []) as $a): ?>
    <tr>
      <td><?= htmlspecialchars($a['starts_at']) ?></td>
      <td><?= htmlspecialchars(($a['first_name'] ?? '').' '.($a['last_name'] ?? '')) ?></td>
      <td><?= htmlspecialchars($a['reason'] ?? '') ?></td>
      <td class="text-end">
        <form method="post" action="<?= APP_URL ?>/doctor/claim" class="d-inline">
          <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
          <input type="hidden" name="appointment_id" value="<?= (int)$a['id'] ?>">
          <button class="btn btn-sm btn-outline-primary">Claim</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

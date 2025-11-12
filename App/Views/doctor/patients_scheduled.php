<h1 class="h4 mb-3">Doctor · Patients (Scheduled)</h1>

<div class="mb-2 d-flex gap-2">
  <a class="btn btn-outline-secondary btn-sm" href="<?= APP_URL ?>/doctor/appointments">Appointments</a>
  <a class="btn btn-outline-secondary btn-sm" href="<?= APP_URL ?>/lab/pending">Lab Pending</a>
  <a class="btn btn-outline-secondary btn-sm" href="<?= APP_URL ?>/lab/completed">Lab Completed</a>
</div>

<div class="card">
  <table class="table mb-0">
    <thead>
      <tr>
        <th>#</th>
        <th>Patient</th>
        <th>Code</th>
        <th>Next Appointment</th>
        <th class="text-center">Upcoming</th>
        <th class="text-end">Quick Links</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach (($rows ?? []) as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?= htmlspecialchars(($r['first_name'] ?? '').' '.($r['last_name'] ?? '')) ?></td>
          <td><?= htmlspecialchars($r['code'] ?? '—') ?></td>
          <td><?= htmlspecialchars($r['next_visit'] ?? '—') ?></td>
          <td class="text-center">
            <span class="badge bg-secondary"><?= (int)($r['upcoming_count'] ?? 0) ?></span>
          </td>
          <td class="text-end d-flex justify-content-end gap-2">
            <a class="btn btn-sm btn-outline-primary"
               href="<?= APP_URL ?>/doctor/lab-order?patient_id=<?= (int)$r['id'] ?>">
              Order Lab Test
            </a>
            <a class="btn btn-sm btn-outline-secondary"
               href="<?= APP_URL ?>/lab/patient?id=<?= (int)$r['id'] ?>">
              Lab Panel
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?>
        <tr><td colspan="6" class="text-muted">No upcoming scheduled patients.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

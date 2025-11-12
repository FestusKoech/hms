<h1 class="h4 mb-3">Doctor · Appointments</h1>

<div class="mb-2 d-flex gap-2">
  <a class="btn btn-outline-secondary btn-sm" href="<?= APP_URL ?>/lab/pending">Lab Pending</a>
  <a class="btn btn-outline-secondary btn-sm" href="<?= APP_URL ?>/lab/completed">Lab Completed</a>
</div>

<div class="card">
  <table class="table mb-0">
    <thead>
      <tr>
        <th>#</th>
        <th>When</th>
        <th>Patient</th>
        <th>Code</th>
        <th>Status</th>
        <th class="text-end">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach (($rows ?? []) as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?= htmlspecialchars($r['scheduled_at'] ?? '—') ?></td>
          <td><?= htmlspecialchars(($r['first_name'] ?? '').' '.($r['last_name'] ?? '')) ?></td>
          <td><?= htmlspecialchars($r['code'] ?? '—') ?></td>
          <td>
            <span class="badge bg-<?= ($r['status'] ?? '') === 'seen' ? 'success' : 'secondary' ?>">
              <?= htmlspecialchars($r['status'] ?? 'scheduled') ?>
            </span>
          </td>
          <td class="text-end">
            <!-- If you already have a doctor patient page, link it here; else leave as lab order shortcut -->
            <a class="btn btn-sm btn-outline-primary"
               href="<?= APP_URL ?>/doctor/lab-order?patient_id=<?= (int)$r['patient_id'] ?>">
              Order Lab Test
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?>
        <tr><td colspan="6" class="text-muted">No upcoming appointments.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

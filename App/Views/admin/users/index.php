<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4">Manage Staff</h1>
  <a class="btn btn-primary shadow-hover" href="<?= APP_URL ?>/admin/users/create">Add Staff</a>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Created</th><th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($items as $u): ?>
        <tr>
          <td><?= (int)$u['id'] ?></td>
          <td><?= htmlspecialchars($u['name']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><span class="badge text-bg-light border"><?= htmlspecialchars($u['role']) ?></span></td>
          <td class="text-muted"><?= htmlspecialchars($u['created_at']) ?></td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-secondary" href="<?= APP_URL ?>/admin/users/edit?id=<?= (int)$u['id'] ?>">Edit</a>
            <form class="d-inline" method="post" action="<?= APP_URL ?>/admin/users/delete" onsubmit="return confirm('Delete this user?')">
              <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
              <button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

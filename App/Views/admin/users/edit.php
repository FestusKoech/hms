<h1 class="h4 mb-3">Edit Staff User</h1>
<form method="post" action="<?= APP_URL ?>/admin/users/update" class="card card-body shadow-hover">
  <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
  <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
  <div class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Full name</label>
      <input class="form-control" name="name" value="<?= htmlspecialchars($u['name']) ?>" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Email</label>
      <input class="form-control" type="email" name="email" value="<?= htmlspecialchars($u['email']) ?>" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Role</label>
      <select class="form-select" name="role" required>
        <?php foreach(['doctor','pharmacist','labtech','receptionist','admin'] as $r): ?>
          <option value="<?= $r ?>" <?= $u['role']===$r?'selected':'' ?>><?= $r ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Reset password (optional)</label>
      <input class="form-control" type="text" name="password" placeholder="leave blank to keep current">
    </div>
  </div>
  <div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary">Update</button>
    <a class="btn btn-outline-light" href="<?= APP_URL ?>/admin/users">Cancel</a>
  </div>
</form>

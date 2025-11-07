<h1 class="h4 mb-3">Add Staff User</h1>
<form method="post" action="<?= APP_URL ?>/admin/users/store" class="card card-body shadow-hover">
  <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
  <div class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Full name</label>
      <input class="form-control" name="name" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Email</label>
      <input class="form-control" type="email" name="email" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Role</label>
      <select class="form-select" name="role" required>
        <option value="doctor">doctor</option>
        <option value="pharmacist">pharmacist</option>
        <option value="labtech">lab_technician</option>
        <option value="receptionist">receptionist</option>
        <option value="admin">admin</option>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Temporary password</label>
      <input class="form-control" type="text" name="password" value="Admin@123" required>
      <small class="text-muted">User should change this after first login.</small>
    </div>
  </div>
  <div class="mt-3"><button class="btn btn-primary">Save</button></div>
</form>

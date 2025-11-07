<?php use App\Core\Auth; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>HMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{
      --surface: #ffffff;
      --surface-2: #f7f7f9;
      --border: #e6e6ea;
      --text: #1a1a1c;
      --muted: #6b6f76;
      --accent: #0f62fe; /* subtle blue for focus ring only */
      --shadow: 0 2px 8px rgba(16,24,40,.06), 0 1px 3px rgba(16,24,40,.04);
      --shadow-lg: 0 8px 28px rgba(16,24,40,.10), 0 2px 8px rgba(16,24,40,.06);
      --hover: #f2f4f7;
    }
    body{ background: var(--surface-2); color: var(--text); }
    .navbar{ background: var(--surface); box-shadow: var(--shadow); }
    .card{ background: var(--surface); border: 1px solid var(--border); box-shadow: var(--shadow); }
    .card-header{ background: var(--surface-2); border-bottom: 1px solid var(--border); }
    .form-control, .form-select{ border-color: var(--border); }
    .form-control:focus, .form-select:focus{
      border-color: var(--accent); box-shadow: 0 0 0 .25rem rgba(15,98,254,.15);
    }
    .btn-primary{ background:#2f2f33; border-color:#2f2f33; }
    .btn-primary:hover{ background:#3a3a3f; border-color:#3a3a3f; }
    .btn-outline-light{ color:#2f2f33; border-color: var(--border); background: #fff; }
    .btn-outline-light:hover{ background: var(--hover); border-color: #d6d6db; }
    .table tbody tr:hover{ background: var(--hover); }
    .nav-link{ color:#2f2f33; }
    .nav-link:hover{ color:#000; }
    .shadow-hover:hover{ box-shadow: var(--shadow-lg); transition: box-shadow .2s ease; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg border-bottom">
  <div class="container">
    <a class="navbar-brand" href="<?= APP_URL ?>/dashboard">HMS</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nv"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nv">
      <?php if(Auth::user()): $role=Auth::user()['role']; ?>
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/patients">Patients</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/appointments">Appointments</a></li>

        <?php if(in_array($role,['doctor','admin'])): ?>
          <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/doctor">Doctor</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/doctor/lab-reports">Lab Reports</a></li>
        <?php endif; ?>

        <?php if(in_array($role,['labtech','admin'])): ?>
          <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/lab">Lab</a></li>
        <?php endif; ?>

        <?php if(in_array($role,['pharmacist','admin'])): ?>
          <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/pharmacy/drugs">Pharmacy</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/pharmacy/fulfill">Fulfill Rx</a></li>
        <?php endif; ?>

        <?php if(in_array($role,['receptionist','admin'])): ?>
          <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/reception/patients">Reception</a></li>
        <?php endif; ?>

        <?php if($role==='admin'): ?>
          <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/admin/users">Manage Staff</a></li>
        <?php endif; ?>
      </ul>
      <div class="d-flex align-items-center gap-3">
        <span class="text-muted small"><?= htmlspecialchars(Auth::user()['name']) ?> (<?= $role ?>)</span>
        <a class="btn btn-sm btn-outline-light" href="<?= APP_URL ?>/logout">Logout</a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</nav>
<main class="container py-4">
  <?= $content ?>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

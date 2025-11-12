<?php use App\Core\Auth; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Afia Hospital</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{
      --surface:#ffffff; --surface-2:#f7f7f9; --border:#d1fae5;
      --text:#1a1a1c; --muted:#64748b;
      --accent:#059669; --accent-hover:#047857; --accent-2:#ecfdf5;
      --hover:#f0fdfa; --shadow:0 2px 8px rgba(16,24,40,.06), 0 1px 3px rgba(16,24,40,.04);
      --sidebar-width:270px;
    }
    body{ background:var(--surface-2); color:var(--text); display:flex; min-height:100vh; overflow-x:hidden; }
    .sidebar{ width:var(--sidebar-width); background:linear-gradient(180deg,#ffffff 0%, #f6fffb 100%);
      border-right:1px solid var(--border); box-shadow:var(--shadow);
      position:fixed; inset:0 auto 0 0; overflow-y:auto; transition:width .25s ease; z-index:1000; }
    .sidebar.min{ width:78px; }
    .brand{ display:flex; align-items:center; justify-content:space-between; gap:.5rem;
      padding:1rem .9rem; border-bottom:1px solid var(--border); background:#fff; position:sticky; top:0; z-index:1; }
    .brand .title{ font-weight:700; letter-spacing:.2px; }
    .toggle{ border:none; background:transparent; font-size:1.2rem; color:var(--text); }
    .group-label{ font-size:.72rem; text-transform:uppercase; letter-spacing:.08em; color:var(--muted);
      padding:.75rem .95rem .25rem; margin-top:.25rem; }
    .nav-section{ padding:.25rem .5rem .75rem; }
    .nav-link{ color:var(--text); border-radius:.6rem; padding:.6rem .7rem; margin:.15rem .45rem;
      display:flex; align-items:center; gap:.7rem; font-size:.95rem; border:1px solid transparent; }
    .nav-link:hover{ background:var(--hover); color:var(--accent-hover); }
    .nav-link.active{ background:var(--accent-2); border-color:var(--border); color:var(--accent-hover);
      font-weight:500; box-shadow:0 0 0 2px var(--accent-2) inset; }
    .nav-icon{ width:22px; text-align:center; font-size:1.1rem; color:var(--accent-hover); }
    .main{ flex:1; margin-left:var(--sidebar-width); transition:margin-left .25s ease; }
    .sidebar.min + .main{ margin-left:78px; }
    .topbar{ background:#fff; border-bottom:1px solid var(--border); box-shadow:var(--shadow);
      padding:.75rem 1.25rem; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:900; }
    .userchip{ color:var(--muted); }
    .btn-logout{ border-color:var(--border); }
    .content{ padding:1.25rem; }
    /* guest (no-auth) layout: no sidebar margin */
    .main.guest{ margin-left:0; }
  </style>
</head>
<body>
<?php
  $user = Auth::user();               // may be null
  $role = $user['role'] ?? '';        // safe default
  $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
  $is = function(string $prefix) use ($path){ return str_starts_with($path, $prefix) ? 'active' : ''; };
?>

<!-- Sidebar only when logged in -->
<?php if ($user): ?>
<nav class="sidebar" id="sidebar">
  <div class="brand">
    <span class="d-inline-flex align-items-center gap-2">
      <i class="bi bi-hospital" style="color:#dc2626"></i> <span class="title">Afia Hospital</span>
    </span>
    <button class="toggle" id="sidebarToggle" aria-label="Toggle sidebar"><i class="bi bi-layout-sidebar"></i></button>
  </div>

  <div class="nav-section">
    <div class="group-label">General</div>
    <a href="<?= APP_URL ?>/dashboard" class="nav-link <?= $is(APP_URL.'/dashboard') ?>"><i class="bi bi-house nav-icon"></i> Dashboard</a>
    <a href="<?= APP_URL ?>/patients" class="nav-link <?= $is(APP_URL.'/patients') ?>"><i class="bi bi-person nav-icon"></i> Patients</a>
    <a href="<?= APP_URL ?>/appointments" class="nav-link <?= $is(APP_URL.'/appointments') ?>"><i class="bi bi-calendar nav-icon"></i> Appointments</a>
  </div>

  <?php if(in_array($role,['doctor','admin'])): ?>
  <div class="nav-section">
    <div class="group-label">Doctor</div>
    <a href="<?= APP_URL ?>/doctor/appointments" class="nav-link <?= $is(APP_URL.'/doctor/appointments') ?>"><i class="bi bi-calendar-check nav-icon"></i> Appointments</a>
    <a href="<?= APP_URL ?>/doctor/patients" class="nav-link <?= $is(APP_URL.'/doctor/patients') ?>"><i class="bi bi-people nav-icon"></i> Patients (Scheduled)</a>
    <a href="<?= APP_URL ?>/doctor/lab-order" class="nav-link <?= $is(APP_URL.'/doctor/lab-order') ?>"><i class="bi bi-plus-circle-dotted nav-icon"></i> New Lab Order</a>
    <a href="<?= APP_URL ?>/lab/completed" class="nav-link <?= $is(APP_URL.'/lab/completed') ?>"><i class="bi bi-clipboard2-check nav-icon"></i> Lab Reports</a>
  </div>
  <?php endif; ?>

  <?php if(in_array($role,['labtech','admin'])): ?>
  <div class="nav-section">
    <div class="group-label">Lab</div>
    <a href="<?= APP_URL ?>/lab/search" class="nav-link <?= $is(APP_URL.'/lab/search') ?>"><i class="bi bi-search nav-icon"></i> Search</a>
    <a href="<?= APP_URL ?>/lab/pending" class="nav-link <?= $is(APP_URL.'/lab/pending') ?>"><i class="bi bi-hourglass-split nav-icon"></i> Pending</a>
    <a href="<?= APP_URL ?>/lab/completed" class="nav-link <?= $is(APP_URL.'/lab/completed') ?>"><i class="bi bi-check2-circle nav-icon"></i> Completed</a>
  </div>
  <?php endif; ?>

  <?php if(in_array($role,['pharmacist','admin']) || in_array($role,['receptionist','admin'])): ?>
  <div class="nav-section">
    <div class="group-label">Front Desk</div>
    <?php if(in_array($role,['pharmacist','admin'])): ?>
      <a href="<?= APP_URL ?>/pharmacy/drugs" class="nav-link <?= $is(APP_URL.'/pharmacy/drugs') ?>"><i class="bi bi-capsule nav-icon"></i> Pharmacy</a>
      <a href="<?= APP_URL ?>/pharmacy/fulfill" class="nav-link <?= $is(APP_URL.'/pharmacy/fulfill') ?>"><i class="bi bi-clipboard-check nav-icon"></i> Fulfill Rx</a>
    <?php endif; ?>
    <?php if(in_array($role,['receptionist','admin'])): ?>
      <a href="<?= APP_URL ?>/reception/patients" class="nav-link <?= $is(APP_URL.'/reception') ?>"><i class="bi bi-journal-medical nav-icon"></i> Reception</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php if($role==='admin'): ?>
  <div class="nav-section">
    <div class="group-label">Admin</div>
    <a href="<?= APP_URL ?>/admin/users" class="nav-link <?= $is(APP_URL.'/admin/users') ?>"><i class="bi bi-people-gear nav-icon"></i> Manage Staff</a>
  </div>
  <?php endif; ?>
</nav>
<?php endif; ?>

<!-- Main area always renders (so login page shows for guests) -->
<div class="main <?= $user ? '' : 'guest' ?>">
  <div class="topbar">
    <div class="d-flex align-items-center gap-2">
      <?php if($user): ?>
        <button class="toggle" id="sidebarToggleTop" aria-label="Toggle sidebar"><i class="bi bi-list"></i></button>
      <?php endif; ?>
      <span class="fw-semibold">Afia Hospital Management System</span>
    </div>
    <div class="d-flex align-items-center gap-3">
      <?php if($user): ?>
        <span class="userchip small"><?= htmlspecialchars($user['name'] ?? '') ?> (<?= htmlspecialchars($role) ?>)</span>
        <a class="btn btn-sm btn-outline-light btn-logout border" href="<?= APP_URL ?>/logout">Logout</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="content">
    <?= $content ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const sidebar = document.getElementById('sidebar');
  const t1 = document.getElementById('sidebarToggle');
  const t2 = document.getElementById('sidebarToggleTop');
  [t1,t2].forEach(b=> b && sidebar && b.addEventListener('click', ()=> sidebar.classList.toggle('min')));
</script>
</body>
</html>

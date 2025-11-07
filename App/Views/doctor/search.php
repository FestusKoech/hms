<h1 class="h4 mb-3">Find Patient</h1>
<form class="card card-body mb-3" method="post" action="<?= APP_URL ?>/doctor/search">
  <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
  <div class="row g-2 align-items-center">
    <div class="col-md-8"><input class="form-control" name="q" value="<?= htmlspecialchars($q ?? '') ?>" placeholder="Search by patient number, first/last name"></div>
    <div class="col-md-4"><button class="btn btn-primary w-100">Search</button></div>
  </div>
</form>

<?php if(!empty($items)): ?>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>#</th><th>Patient No</th><th>Name</th><th>Contact</th><th></th></tr></thead>
      <tbody>
      <?php foreach($items as $p): ?>
        <tr>
          <td><?= (int)$p['id'] ?></td>
          <td><?= htmlspecialchars($p['patient_no']) ?></td>
          <td><?= htmlspecialchars($p['first_name'].' '.$p['last_name']) ?></td>
          <td><?= htmlspecialchars($p['contact'] ?? '') ?></td>
          <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="<?= APP_URL ?>/doctor/patient?id=<?= (int)$p['id'] ?>">Open</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php elseif(isset($q) && $q!==''): ?>
<div class="alert alert-warning">No patients found.</div>
<?php endif; ?>

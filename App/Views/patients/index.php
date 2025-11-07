<style>
.row-actions{opacity:0;transition:opacity .15s ease}
.table tbody tr:hover .row-actions{opacity:1}
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Patients</h1>
  <a class="btn btn-primary" href="<?= APP_URL ?>/patients/create">New Patient</a>
</div>

<form class="card card-body mb-3" method="get" action="<?= APP_URL ?>/patients">
  <div class="row g-2">
    <div class="col-md-9">
      <input class="form-control" name="q" value="<?= htmlspecialchars($q ?? '') ?>" placeholder="Search by patient number, first or last name">
    </div>
    <div class="col-md-3">
      <button class="btn btn-outline-secondary w-100">Search</button>
    </div>
  </div>
</form>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>#</th><th>Patient No</th><th>Name</th><th>Contact</th><th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach(($data ?? []) as $p): ?>
        <tr>
          <td><?= (int)$p['id'] ?></td>
          <td><?= htmlspecialchars($p['patient_no']) ?></td>
          <td><?= htmlspecialchars($p['first_name'].' '.$p['last_name']) ?></td>
          <td><?= htmlspecialchars($p['contact'] ?? '') ?></td>
          <td class="text-end">
            <div class="row-actions">
              <a class="btn btn-sm btn-outline-secondary" href="<?= APP_URL ?>/patients/show?id=<?= (int)$p['id'] ?>">View</a>
              <a class="btn btn-sm btn-outline-primary" href="<?= APP_URL ?>/patients/edit?id=<?= (int)$p['id'] ?>">Edit</a>

              <?php if(in_array(($role ?? ''), ['doctor','admin'])): ?>
                <a class="btn btn-sm btn-primary" href="<?= APP_URL ?>/doctor/prescribe?patient_id=<?= (int)$p['id'] ?>">Add Rx</a>
                <a class="btn btn-sm btn-outline-secondary" href="<?= APP_URL ?>/doctor/lab-order?patient_id=<?= (int)$p['id'] ?>">Lab Order</a>
              <?php endif; ?>

              <?php if(($role ?? '')==='admin'): ?>
                <form class="d-inline" method="post" action="<?= APP_URL ?>/patients/delete" onsubmit="return confirm('Delete patient #<?= (int)$p['id'] ?>?')">
                  <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? \App\Core\Csrf::token()) ?>">
                  <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if(($q ?? '')==='' && ($total ?? 0) > ($per ?? 15)): ?>
  <nav class="mt-3">
    <ul class="pagination pagination-sm mb-0">
      <?php $pages = (int)ceil(($total ?? 0)/($per ?? 15)); for($i=1;$i<=$pages;$i++): ?>
        <li class="page-item <?= $i===(int)($page ?? 1) ? 'active':'' ?>">
          <a class="page-link" href="<?= APP_URL ?>/patients?page=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
<?php endif; ?>

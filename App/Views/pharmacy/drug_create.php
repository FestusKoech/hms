<h1 class="h4 mb-3">Add Drug</h1>
<form class="card card-body" method="post" action="<?= APP_URL ?>/pharmacy/drugs/store">
  <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
  <div class="row g-3">
    <div class="col-md-3"><label class="form-label">SKU</label><input class="form-control" name="sku" required></div>
    <div class="col-md-5"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
    <div class="col-md-2"><label class="form-label">Form</label><input class="form-control" name="form"></div>
    <div class="col-md-2"><label class="form-label">Strength</label><input class="form-control" name="strength"></div>
    <div class="col-md-2"><label class="form-label">Qty On Hand</label><input class="form-control" type="number" name="qty_on_hand" value="0"></div>
    <div class="col-md-2"><label class="form-label">Reorder Level</label><input class="form-control" type="number" name="reorder_level" value="0"></div>
  </div>
  <div class="mt-3"><button class="btn btn-primary">Save</button></div>
</form>

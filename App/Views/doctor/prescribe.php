<h1 class="h4 mb-3">New Prescription</h1>
<form method="post" action="<?= APP_URL ?>/doctor/prescribe" class="card card-body">
  <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
  <input type="hidden" name="patient_id" value="<?= (int)$patient_id ?>">
  <div class="mb-3"><label class="form-label">Notes (optional)</label><textarea class="form-control" name="notes"></textarea></div>

  <div id="rx-items">
    <div class="row g-2 align-items-end mb-2">
      <div class="col-md-4">
        <label class="form-label">Drug</label>
        <select class="form-select" name="items[0][drug_id]">
          <?php foreach($drugs as $d): ?>
            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name'].' '.$d['strength']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2"><label class="form-label">Dosage</label><input class="form-control" name="items[0][dosage]" placeholder="1 tab"></div>
      <div class="col-md-2"><label class="form-label">Frequency</label><input class="form-control" name="items[0][frequency]" placeholder="t.i.d."></div>
      <div class="col-md-2"><label class="form-label">Days</label><input class="form-control" type="number" name="items[0][duration]" value="5"></div>
    </div>
  </div>

  <button type="button" class="btn btn-outline-light btn-sm" onclick="addRow()">+ Add Item</button>
  <div class="mt-3"><button class="btn btn-primary">Save Prescription</button></div>
</form>

<script>
let idx = 1;
function addRow(){
  const html = `
  <div class="row g-2 align-items-end mb-2">
    <div class="col-md-4">
      <select class="form-select" name="items[${idx}][drug_id]">
        <?php foreach($drugs as $d): ?>
          <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name'].' '.$d['strength']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2"><input class="form-control" name="items[${idx}][dosage]" placeholder="1 tab"></div>
    <div class="col-md-2"><input class="form-control" name="items[${idx}][frequency]" placeholder="t.i.d."></div>
    <div class="col-md-2"><input class="form-control" type="number" name="items[${idx}][duration]" value="5"></div>
  </div>`;
  document.getElementById('rx-items').insertAdjacentHTML('beforeend', html);
  idx++;
}
</script>

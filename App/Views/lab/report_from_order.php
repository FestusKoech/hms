<h1 class="h4 mb-3">Report for Order #<?= (int)$order['id'] ?></h1>
<form method="post" action="<?= APP_URL ?>/lab/report-from-order" class="card card-body">
  <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
  <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
  <div class="row g-3">
    <div class="col-md-4"><label class="form-label">Result (Value)</label><input class="form-control" name="result_value"></div>
    <div class="col-md-8"><label class="form-label">Result (Text)</label><input class="form-control" name="result_text"></div>
  </div>
  <div class="mt-3"><button class="btn btn-primary">Save Report</button></div>
</form>

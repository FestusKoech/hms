<?php
// Simple printable slip with no medical details
$pn = trim(($a['first_name'] ?? '').' '.($a['last_name'] ?? ''));
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Appointment Slip #<?= (int)$a['id'] ?></title>
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 24px; }
    .card { border:1px solid #e5e7eb; border-radius:10px; padding:20px; max-width:520px; }
    h1 { font-size:18px; margin:0 0 12px; }
    .row { margin:6px 0; }
    .muted { color:#6b7280; }
    @media print { .noprint { display:none; } }
  </style>
</head>
<body>
  <div class="card">
    <h1>Appointment Slip</h1>
    <div class="row"><strong>Patient:</strong> <?= htmlspecialchars($pn) ?> (<?= htmlspecialchars($a['code'] ?? '') ?>)</div>
    <div class="row"><strong>Doctor:</strong> <?= htmlspecialchars($a['doctor']) ?></div>
    <div class="row"><strong>Starts:</strong> <?= htmlspecialchars($a['starts_at']) ?></div>
    <div class="row"><strong>Ends:</strong> <?= htmlspecialchars($a['ends_at']) ?></div>
    <div class="row"><strong>Status:</strong> <?= htmlspecialchars($a['status']) ?></div>
    <div class="row muted">Slip #: <?= (int)$a['id'] ?></div>
    <div class="noprint" style="margin-top:16px">
      <button onclick="window.print()">Print</button>
    </div>
  </div>
</body>
</html>

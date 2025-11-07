<?php
use App\Core\DB;

// Quick inline stats so you don't have to change controllers
$patients      = (int) DB::pdo()->query("SELECT COUNT(*) FROM patients")->fetchColumn();

$pendingLabs   = (int) DB::pdo()->query("SELECT COUNT(*) FROM lab_orders WHERE status IN ('ordered','pending')")->fetchColumn();
$completedLabs = (int) DB::pdo()->query("SELECT COUNT(*) FROM lab_reports WHERE reported_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();

$pendingRx     = (int) DB::pdo()->query("
  SELECT COUNT(*) FROM prescription_items i
  JOIN prescriptions p ON p.id=i.prescription_id
  WHERE i.dispensed=0 AND p.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
")->fetchColumn();

$dispensedRx   = (int) DB::pdo()->query("
  SELECT COUNT(*) FROM prescription_items i
  JOIN prescriptions p ON p.id=i.prescription_id
  WHERE i.dispensed=1 AND p.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
")->fetchColumn();

$lowStock      = (int) DB::pdo()->query("SELECT COUNT(*) FROM drugs WHERE stock <= 10")->fetchColumn();

// Visits (last 7 days)
$rows = DB::pdo()->query("
  SELECT DATE(starts_at) d, COUNT(*) c
  FROM appointments
  WHERE starts_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
  GROUP BY DATE(starts_at)
  ORDER BY d
")->fetchAll();

$labels = []; $values = []; $map = [];
foreach ($rows as $r) { $map[$r['d']] = (int)$r['c']; }
$period = new \DatePeriod(new \DateTime('-6 days 00:00:00'), new \DateInterval('P1D'), 7);
foreach ($period as $dt) {
  $key = $dt->format('Y-m-d');
  $labels[] = $dt->format('D');
  $values[] = $map[$key] ?? 0;
}
?>
<style>
.hero {
  position: relative; border-radius: 20px; overflow: hidden; background: #000;
  min-height: 220px; display: grid; place-items: center;
  box-shadow: 0 8px 28px rgba(16,24,40,.10), 0 2px 8px rgba(16,24,40,.06);
}
.hero::before{ content:""; position:absolute; inset:0;
  background: url('<?= APP_URL ?>/assets/afia-hero.jpg') center/cover no-repeat;
  opacity:.40; filter: saturate(110%) contrast(105%);
}
.hero::after{ content:""; position:absolute; inset:0;
  background: radial-gradient(ellipse at center, rgba(255,255,255,.15), rgba(0,0,0,.45));
}
.hero-inner{ position:relative; z-index:1; text-align:center; color:#fff; }
.hero h1{ margin:0; font-weight:700; letter-spacing:.5px; text-shadow:0 4px 18px rgba(0,0,0,.35); }
.subtitle{ opacity:.95; font-weight:500; }

.card-kpi{ background:#fff; border:1px solid #e6e6ea; border-radius:16px;
  box-shadow:0 2px 8px rgba(16,24,40,.06), 0 1px 3px rgba(16,24,40,.04);
}
.kpi-value{ font-size:28px; font-weight:700; }
.kpi-label{ color:#6b6f76; font-size:12px; text-transform:uppercase; letter-spacing:.06em; }
.section-title{ font-weight:700; }
</style>

<div class="hero mb-4">
  <div class="hero-inner py-5">
    <h1 class="display-6">Afia Health Records</h1>
    <div class="subtitle">Clinical Overview • Staff Console</div>
  </div>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-4 col-xl-2"><div class="card card-kpi p-3 shadow-hover">
    <div class="kpi-value"><?= $patients ?></div><div class="kpi-label">Patients</div></div></div>
  <div class="col-6 col-md-4 col-xl-2"><div class="card card-kpi p-3 shadow-hover">
    <div class="kpi-value"><?= $pendingLabs ?></div><div class="kpi-label">Pending Labs</div></div></div>
  <div class="col-6 col-md-4 col-xl-2"><div class="card card-kpi p-3 shadow-hover">
    <div class="kpi-value"><?= $completedLabs ?></div><div class="kpi-label">Labs (30d)</div></div></div>
  <div class="col-6 col-md-4 col-xl-2"><div class="card card-kpi p-3 shadow-hover">
    <div class="kpi-value"><?= $pendingRx ?></div><div class="kpi-label">Rx Pending (30d)</div></div></div>
  <div class="col-6 col-md-4 col-xl-2"><div class="card card-kpi p-3 shadow-hover">
    <div class="kpi-value"><?= $dispensedRx ?></div><div class="kpi-label">Rx Dispensed (30d)</div></div></div>
  <div class="col-6 col-md-4 col-xl-2"><div class="card card-kpi p-3 shadow-hover">
    <div class="kpi-value"><?= $lowStock ?></div><div class="kpi-label">Low Stock</div></div></div>
</div>

<!-- Charts -->
<div class="row g-3">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span class="section-title">Visits (last 7 days)</span>
      </div>
      <div class="card-body" style="height:260px">
        <canvas id="visitsChart"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span class="section-title">Prescriptions (30 days)</span>
      </div>
      <div class="card-body" style="height:240px">
        <canvas id="rxChart"></canvas>
      </div>
      <div class="small text-muted px-3 pb-3">
        Pending: <?= $pendingRx ?> &nbsp;|&nbsp; Dispensed: <?= $dispensedRx ?>
      </div>
    </div>
  </div>
</div>

<!-- Chart.js (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Visits bar chart
  const visitsCanvas = document.getElementById('visitsChart');
  if (visitsCanvas) {
    visitsCanvas.parentElement.style.height = '260px';
    const visitsCtx = visitsCanvas.getContext('2d');
    new Chart(visitsCtx, {
      type: 'bar',
      data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{ label: 'Visits', data: <?= json_encode($values) ?>, borderWidth: 1 }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: { y: { beginAtZero: true } },
        plugins: { legend: { display: false } }
      }
    });
  }

  // Rx doughnut chart
  const rxCanvas = document.getElementById('rxChart');
  if (rxCanvas) {
    rxCanvas.parentElement.style.height = '240px';
    const pendingRx   = Number(<?= (int)$pendingRx ?>);
    const dispensedRx = Number(<?= (int)$dispensedRx ?>);
    const useFallback = (pendingRx + dispensedRx === 0);
    const rxData      = useFallback ? [1,1] : [pendingRx, dispensedRx];

    const rxCtx = rxCanvas.getContext('2d');
    new Chart(rxCtx, {
      type: 'doughnut',
      data: { labels: ['Pending','Dispensed'], datasets: [{ data: rxData, borderWidth: 1 }] },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        cutout: '60%',
        plugins: {
          legend: { position: 'bottom' },
          tooltip: {
            callbacks: {
              label: (ctx) => {
                const real = ctx.dataIndex === 0 ? pendingRx : dispensedRx;
                return `${ctx.label}: ${real}`;
              }
            }
          }
        }
      }
    });

    if (useFallback) {
      const note = document.createElement('div');
      note.className = 'small text-muted mt-2';
      note.textContent = 'No prescription activity in the last 30 days yet.';
      rxCanvas.parentElement.appendChild(note);
    }
  }
});
</script>

<?php
/** @var array $rep */
// $rep keys used: id, test_name, first_name, last_name, code, reported_by_name, reported_at, result_value, result_text
$r = $rep ?? [];
?>
<?php if(!empty($_SESSION['flash'])): ?>
  <div class="alert alert-success small">
    <?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?>
  </div>
<?php endif; ?>

<!-- Sticky toolbar (stays on top) -->
<!-- Simple page header (non-sticky, no duplicate nav) -->
<div class="d-flex align-items-center justify-content-between mb-3">
  <div class="d-flex align-items-center gap-2">
    <a class="btn btn-sm btn-outline-secondary" href="<?= APP_URL ?>/lab/pending">← Back</a>
    <h1 class="h5 mb-0">
      Lab Report <span class="text-muted">· #<?= (int)($r['id'] ?? 0) ?></span>
    </h1>
  </div>
  <?php if(!empty($r['code'])): ?>
    <span class="badge bg-light text-dark border">Patient: <?= htmlspecialchars($r['code']) ?></span>
  <?php endif; ?>
</div>


<!-- Inline Quick Panel (placeholder; hidden by default) -->
<!-- <div id="quickPanel" class="card mb-3 d-none">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span id="qpTitle" class="fw-semibold">Quick Panel</span>
    <button type="button" class="btn-close" aria-label="Close"
            onclick="document.getElementById('quickPanel').classList.add('d-none')"></button>
  </div>
  <div id="qpBody" class="card-body">
    <div class="text-muted small">Use the toolbar buttons above to load content here.</div>
  </div>
</div> -->

<div class="container-fluid px-0">
  <div class="row g-3">


  <!-- Left: Report content -->
  <div class="col-12 col-lg-8">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div>
          <div class="fw-semibold"><?= htmlspecialchars($r['test_name'] ?? '—') ?></div>
          <div class="small text-muted">Final results as reported below.</div>
        </div>
        <div class="text-end">
          <div class="small text-muted">Reported by</div>
          <div class="fw-semibold"><?= htmlspecialchars($r['reported_by_name'] ?? '—') ?></div>
          <div class="small text-muted"><?= htmlspecialchars($r['reported_at'] ?? '—') ?></div>
        </div>
      </div>

      <div class="card-body">
        <div class="mb-3">
          <div class="small text-muted">Result Value</div>
          <div class="fs-5 fw-semibold"><?= htmlspecialchars($r['result_value'] ?? '—') ?></div>
        </div>

        <div>
          <div class="small text-muted">Notes / Interpretation</div>
          <div class="border rounded p-3" style="min-height: 96px;">
            <?= nl2br(htmlspecialchars($r['result_text'] ?? '—')) ?>
          </div>
        </div>
      </div>

      <div class="card-footer d-flex align-items-center justify-content-between">
        <div class="small text-muted">
          Test: <?= htmlspecialchars($r['test_name'] ?? '—') ?>
        </div>
        <div class="d-flex gap-2">
          <a class="btn btn-outline-secondary btn-sm" href="<?= APP_URL ?>/lab/completed">Back</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Right: Patient + meta -->
  <div class="col-12 col-lg-4">
    <div class="card">
      <div class="card-header">Patient</div>
      <div class="card-body">
        <div class="mb-2">
          <div class="small text-muted">Name</div>
          <div class="fw-semibold">
            <?= htmlspecialchars(($r['first_name'] ?? '').' '.($r['last_name'] ?? '')) ?>
            <span class="text-muted">· <?= htmlspecialchars($r['code'] ?? '') ?></span>
          </div>
        </div>
        <div class="mb-2">
          <div class="small text-muted">Report ID</div>
          <div>#<?= (int)($r['id'] ?? 0) ?></div>
        </div>
        <div class="mb-2">
          <div class="small text-muted">Status</div>
          <span class="badge bg-success">reported</span>
        </div>
      </div>
      
    </div>
  </div>
</div>

  </div> <!-- /.row -->
</div>   <!-- /.container-fluid -->

<script>
(function(){
  const panel   = document.getElementById('quickPanel');
  const body    = document.getElementById('qpBody');
  const titleEl = document.getElementById('qpTitle');

  // click handlers for toolbar buttons
  document.addEventListener('click', async function(e){
    const btn = e.target.closest('[data-ajax-panel]');
    if (!btn) return;
    e.preventDefault();

    const url   = btn.getAttribute('data-url');
    const title = btn.getAttribute('data-title') || 'Quick Panel';

    await loadIntoPanel(url, title);
  });

  // submit handler for the toolbar search form
  window.openQuickPanel = async function(ev, baseUrl, title, q){
    ev.preventDefault();
    if (!q || !q.trim()) return;
    const url = baseUrl + (baseUrl.includes('?') ? '&' : '?') + 'q=' + encodeURIComponent(q.trim());
    await loadIntoPanel(url, title);
  };

  async function loadIntoPanel(url, title){
    panel.classList.remove('d-none');
    titleEl.textContent = title;
    body.innerHTML = '<div class="text-muted small">Loading…</div>';
    try {
      // Optionally append ?partial=1 if your routes support partial rendering.
      const res = await fetch(url, { credentials: 'same-origin' });
      const html = await res.text();
      body.innerHTML = html;
    } catch (err) {
      body.innerHTML = '<div class="text-danger small">Failed to load content.</div>';
    }
    // Optional: scroll to panel when opened
    panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
})();
</script>

<?php /* Doctor Dashboard — Quick Links only */ ?>
<style>
.sticky-toolbar {
  position: sticky; top: 0; z-index: 1030;
  background: #fff; border: 1px solid #e6e6ea; border-radius: 12px;
  box-shadow: 0 2px 8px rgba(16,24,40,.06), 0 1px 3px rgba(16,24,40,.04);
}
.sticky-toolbar .btn { border-radius: 999px; }
#workPane { min-height: 380px; }
.loader { display: grid; place-items: center; min-height: 260px; color: #6b6f76; }

/* Optional tidy header */
.hero-mini {
  border-radius: 14px; background: linear-gradient(135deg,#111,#333);
  color:#fff; padding:18px 20px; margin-bottom:14px;
  box-shadow: 0 6px 18px rgba(0,0,0,.12);
}
.hero-mini h2 { margin:0; font-size:18px; font-weight:700; letter-spacing:.3px; }
.hero-mini .sub { opacity:.9; font-size:13px; }
</style>

<div class="hero-mini">
  <h2>Doctor Console</h2>
  <div class="sub">Quick access to Patients, Labs, Pharmacy and Orders</div>
</div>

<!-- Fixed Quick Links (single work pane) -->
<div class="p-3 sticky-toolbar mb-3 d-flex flex-wrap align-items-center gap-2">
  <span class="section-title mb-0 me-2">Quick Links</span>
  <div class="btn-group" role="group" aria-label="Quick Links">
    <button type="button" class="btn btn-primary active"
            data-url="<?= APP_URL ?>/doctor/search">Search Patients</button>
    <button type="button" class="btn btn-outline-secondary"
            data-url="<?= APP_URL ?>/patients">All Patients</button>
    <button type="button" class="btn btn-outline-secondary"
            data-url="<?= APP_URL ?>/doctor/lab-reports">Lab Activity</button>
    <button type="button" class="btn btn-outline-secondary"
            data-url="<?= APP_URL ?>/pharmacy/fulfill">Pharmacy</button>
    <button type="button" class="btn btn-outline-secondary"
            data-url="<?= APP_URL ?>/lab/orders">Lab Orders</button>
  </div>
</div>

<div class="card">
  <div class="card-body" id="workPane">
    <div class="loader">Loading…</div>
  </div>
</div>

<script>
(function(){
  const pane = document.getElementById('workPane');
  const toolbar = document.querySelector('.sticky-toolbar .btn-group');
  if (!pane || !toolbar) return;

  const DASHBOARD_PATHS = new Set([
    '<?= APP_URL ?>/doctor',
    '<?= APP_URL ?>/doctor/',
    '<?= APP_URL ?>/doctor/dashboard'
  ]);

  async function loadIntoPane(url) {
    if (!url) return;
    try { url = new URL(url, window.location.origin).toString(); } catch(e){}

    // prevent loading dashboard into itself
    if (DASHBOARD_PATHS.has(url) || url.includes('/doctor/dashboard')) return;

    pane.innerHTML = '<div class="loader">Loading…</div>';
    try {
      const res = await fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (!res.ok) { pane.innerHTML = `<div class="text-danger">Failed (${res.status}).</div>`; return; }
      let html = await res.text();

      // extract <body> if full doc
      try {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        if (doc?.body?.innerHTML.trim()) html = doc.body.innerHTML;
      } catch(e){}

      pane.innerHTML = html || '<div class="text-muted">No content.</div>';

      // strip any navbars that came with the inner page
      pane.querySelectorAll('nav, .navbar, header').forEach(el => el.remove());

      wireInternalNav(url);
    } catch (e) {
      console.error(e);
      pane.innerHTML = '<div class="text-danger">Could not load content.</div>';
    }
  }

  function absolutize(href, base) {
    try { return new URL(href, base || window.location.href).toString(); }
    catch { return href; }
  }

  function wireInternalNav(currentUrl) {
    // Links
    pane.querySelectorAll('a[href]').forEach(a => {
      const raw = a.getAttribute('href') || '#';
      if (raw.startsWith('#') || raw.startsWith('javascript:')) return;
      const abs = absolutize(raw, currentUrl);
      if (!abs.startsWith(window.location.origin)) return;

      a.addEventListener('click', (e) => {
        const target = (a.getAttribute('target') || '').toLowerCase();
        if (target === '_blank') return;
        if (DASHBOARD_PATHS.has(abs) || abs.includes('/doctor/dashboard')) return;
        e.preventDefault();
        setActiveByUrl(abs);
        loadIntoPane(abs);
      });
    });

    // GET forms
    pane.querySelectorAll('form').forEach(form => {
      const method = (form.getAttribute('method') || 'GET').toUpperCase();
      if (method !== 'GET') return; // let POST navigate normally
      form.addEventListener('submit', (e) => {
        e.preventDefault();
        const action = absolutize(form.getAttribute('action') || currentUrl, currentUrl);
        const qs = new URLSearchParams(new FormData(form)).toString();
        const next = qs ? `${action}?${qs}` : action;
        if (DASHBOARD_PATHS.has(next) || next.includes('/doctor/dashboard')) return;
        setActiveByUrl(next);
        loadIntoPane(next);
      });
    });
  }

  function setActive(btn) {
    toolbar.querySelectorAll('.btn').forEach(b => {
      b.classList.remove('btn-primary','active');
      b.classList.add('btn-outline-secondary');
    });
    btn.classList.remove('btn-outline-secondary');
    btn.classList.add('btn-primary','active');
  }

  function setActiveByUrl(url) {
    const buttons = Array.from(toolbar.querySelectorAll('.btn'));
    const found = buttons.find(b => {
      const u = b.getAttribute('data-url');
      return u && url.startsWith(u);
    });
    if (found) setActive(found);
  }

  // Wire buttons
  toolbar.querySelectorAll('.btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const url = btn.getAttribute('data-url');
      setActive(btn);
      loadIntoPane(url);
    });
  });

  // Initial load
  const first = toolbar.querySelector('.btn.active') || toolbar.querySelector('.btn');
  if (first) loadIntoPane(first.getAttribute('data-url'));
})();
</script>

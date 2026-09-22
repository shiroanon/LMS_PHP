<div class="topbar">
  <button id="drawerBtn" class="btn btn-ghost btn-small" style="display:none" onclick="document.getElementById('sidebar').classList.toggle('open')">☰ Menu</button>
  <style>@media(max-width:900px){#drawerBtn{display:inline-flex!important}}</style>
  <div class="search">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input data-barcode-input placeholder="Scan barcode or search title / accession…  (⌘K)" onkeydown="if(event.key==='Enter'){ const v=this.value.trim(); if(v) location.href='/books?search='+encodeURIComponent(v); }">
  </div>
  <div style="margin-left:auto; display:flex; gap:10px; align-items:center">
    <span class="mono" style="font-size:11px; color:var(--slate); letter-spacing:.06em; border:1px solid var(--border); background:white; padding:6px 10px; border-radius:999px">ACC: <b style="color:var(--ink)"><?= date('Ymd') ?>-LMS</b></span>
    <a href="/profile" class="btn btn-ghost btn-small">Profile</a>
  </div>
</div>

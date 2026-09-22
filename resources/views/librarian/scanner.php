<?php $title='Scanner'; ?>
<div class="page-head">
  <div class="eyebrow">Hardware <i></i> wedge</div>
  <h1>Scanner</h1>
  <p>USB wedge scanner acts as keyboard — focus a field and scan. Enter-terminated.</p>
</div>

<div class="catalog-card" style="max-width:640px">
  <div class="perf-notch"></div>
  <div class="card-inner">
    <div class="eyebrow">Wedge test <i></i></div>
    <div class="field" style="margin-top:12px"><label>Scan here (any barcode)</label><input class="input mono" data-barcode-input id="wedgeTest" placeholder="Click here then scan — value appears" autofocus></div>
    <div id="wedgeResult" class="mono" style="margin-top:10px; font-size:12px; color:var(--slate)">Waiting for scan… (Enter-terminated)</div>
    <div style="display:flex; gap:8px; margin-top:12px">
      <a href="/issue" class="btn btn-primary btn-small">Go to Issue</a>
      <a href="/visits" class="btn btn-ghost btn-small">Gate check</a>
      <a href="/barcodes" class="btn btn-ghost btn-small">Barcodes</a>
    </div>
    <p class="mono" style="font-size:11px; color:var(--slate); margin-top:12px">Tip: No driver needed. If nothing appears, click the field and scan again — the global hook fills the focused <span style="color:var(--ink)">[data-barcode-input]</span>.</p>
  </div>
</div>

<script>
const wedge = document.getElementById('wedgeTest');
const res = document.getElementById('wedgeResult');
if(wedge){
  wedge.addEventListener('change', ()=> res.textContent='Scanned: '+wedge.value+' → try Issue/Visits');
  wedge.addEventListener('input', ()=> res.textContent='Buffer: '+wedge.value);
}
</script>

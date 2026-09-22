<?php $title='Barcodes'; ?>
<div class="page-head" style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:end">
  <div>
    <div class="eyebrow">Print <i></i> <span class="mono"><?= (int)$total ?> records<?= $q!=='' ? ' · filtered' : '' ?></span></div>
    <h1>Barcodes <em>— print</em></h1>
    <p>CODE128 via <span class="mono">/barcode/render?text=</span>. Scanner wedge fills focused field. Select individually or print all.</p>
  </div>
  <form method="post" action="/barcodes/generate" style="display:flex; gap:8px">
    <input type="hidden" name="type" value="<?= e($tab) ?>">
    <button class="btn btn-primary btn-small">Bulk generate <?= e($tab) ?></button>
  </form>
</div>

<div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center; margin-bottom:12px">
  <div style="display:flex; gap:8px">
    <a href="/barcodes?tab=students<?= $q!=='' ? '&q='.urlencode($q) : '' ?>" class="btn <?= $tab==='students'?'btn-primary':'btn-ghost' ?> btn-small">Students</a>
    <a href="/barcodes?tab=books<?= $q!=='' ? '&q='.urlencode($q) : '' ?>" class="btn <?= $tab==='books'?'btn-primary':'btn-ghost' ?> btn-small">Books (copies)</a>
  </div>
  <form method="get" action="/barcodes" class="search" style="flex:1; max-width:420px; margin-left:8px">
    <input type="hidden" name="tab" value="<?= e($tab) ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); width:16px; height:16px; color:var(--slate)"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="<?= $tab==='books' ? 'Search title / accession / barcode' : 'Search name / enrollment / barcode' ?>" style="padding-left:38px; width:100%; padding:11px 12px 11px 38px; border:1px solid var(--border); border-radius:12px; background:white; font-size:13px">
  </form>
  <span class="pill pill-slate" style="margin-left:auto"><span class="pill-dot"></span><span id="selCount">0</span> selected</span>
</div>

<div class="catalog-card" style="margin-bottom:12px">
  <div class="card-inner" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center; padding:12px 16px">
    <label style="display:flex; gap:8px; align-items:center; font-size:13px; cursor:pointer"><input type="checkbox" id="selectAll"> Select all on page</label>
    <button type="button" class="btn btn-ghost btn-small" onclick="clearSelection()">Clear</button>
    <div style="margin-left:auto; display:flex; gap:8px">
      <button type="button" class="btn btn-ghost btn-small" onclick="window.print()">Print this page</button>
      <button type="button" id="printSelectedBtn" class="btn btn-primary btn-small" onclick="printSelected()" disabled>Print selected (0)</button>
    </div>
  </div>
</div>

<div id="barcodeGrid" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:14px">
  <?php foreach($rows as $idx=>$r):
    $code = $tab==='books' ? ($r['barcode_data'] ?? $r['accession_no']) : ($r['barcode_data'] ?? $r['enrollment_no'] ?? $r['library_id']);
    $label = $tab==='books' ? $r['title'] : $r['name'];
    $sub = $tab==='books' ? $r['accession_no'] : ($r['enrollment_no'] ?? $r['library_id']);
    $sub2 = $tab==='students' ? ($r['branch'] ?? '') : $r['book_id'] ?? '';
  ?>
  <div class="catalog-card barcode-card" data-code="<?= e($code) ?>" data-label="<?= e($label) ?>" data-sub="<?= e($sub) ?>" style="text-align:center; position:relative">
    <label style="position:absolute; top:10px; left:10px; display:flex; align-items:center; gap:6px; cursor:pointer; background:white; border:1px solid var(--border); border-radius:8px; padding:4px 8px; font-size:11px; font-weight:700">
      <input type="checkbox" class="barcode-check" value="<?= e($code) ?>" data-label="<?= e($label) ?>" onchange="updateSelection()"> Select
    </label>
    <div class="card-inner" style="padding:14px; padding-top:32px">
      <div style="font-size:11px; color:var(--slate); font-weight:700; letter-spacing:.08em; text-transform:uppercase; white-space:nowrap; overflow:hidden; text-overflow:ellipsis" title="<?= e($label) ?>"><?= e($label) ?></div>
      <div class="mono" style="font-size:11px; color:var(--slate)"><?= e($sub) ?><?= $sub2 ? ' · '.e($sub2) : '' ?></div>
      <img src="/barcode/render?text=<?= urlencode($code) ?>" alt="barcode <?= e($code) ?>" style="margin:10px auto; max-width:100%; height:60px; image-rendering:pixelated" loading="lazy">
      <svg class="jsbarcode" data-value="<?= e($code) ?>" style="width:100%; height:56px; display:none"></svg>
      <div class="mono" style="font-size:11px; letter-spacing:.12em; font-weight:700"><?= e($code) ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php if(empty($rows)): ?>
<div class="catalog-card" style="margin-top:14px"><div class="card-inner empty"><div class="stamp">No match</div><h3>No barcodes found</h3><p>Try a different search — title, name, accession or barcode.</p></div></div>
<?php endif; ?>

<?php if($pages>1): ?>
<div style="display:flex; gap:6px; justify-content:center; margin-top:18px; flex-wrap:wrap; align-items:center">
  <?php
    $current=(int)$page; $delta=2; $range=[];
    for($i=1;$i<=$pages;$i++){ if($i==1||$i==$pages||($i>=$current-$delta&&$i<=$current+$delta)) $range[]=$i; elseif(end($range)!=='...') $range[]='...'; }
    $base="/barcodes?tab=".urlencode($tab).($q!==''?'&q='.urlencode($q):'')."&page=";
  ?>
  <a href="<?= $current>1?$base.($current-1):'#' ?>" class="btn btn-ghost btn-small" style="<?= $current<=1?'opacity:.35;pointer-events:none':'' ?>">‹</a>
  <?php foreach($range as $p): if($p==='...'): ?><span style="width:36px; display:flex; align-items:center; justify-content:center; color:var(--slate)">…</span><?php else: ?><a href="<?= $base.$p ?>" class="btn <?= $p==$current?'btn-primary':'btn-ghost' ?> btn-small" style="min-width:36px; justify-content:center"><?= $p ?></a><?php endif; endforeach; ?>
  <a href="<?= $current<$pages?$base.($current+1):'#' ?>" class="btn btn-ghost btn-small" style="<?= $current>=$pages?'opacity:.35;pointer-events:none':'' ?>">›</a>
</div>
<div style="text-align:center; margin-top:6px; font-size:11px; color:var(--slate)" class="mono">Page <?= $current ?> of <?= $pages ?> · <?= (int)$total ?> total<?= $q!=='' ? ' · filtered' : '' ?></div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
document.querySelectorAll('.jsbarcode').forEach(el=>{
  try{ JsBarcode(el, el.dataset.value, {format:"CODE128", height:46, displayValue:false, margin:0}); el.style.display='block'; el.previousElementSibling.style.display='none'; }catch(e){}
});
const checks=document.querySelectorAll('.barcode-check');
const selCount=document.getElementById('selCount');
const printBtn=document.getElementById('printSelectedBtn');
const selectAll=document.getElementById('selectAll');
function updateSelection(){
  const sel=[...checks].filter(c=>c.checked);
  const n=sel.length;
  selCount.textContent=n;
  printBtn.textContent='Print selected ('+n+')';
  printBtn.disabled=n===0;
  selectAll.checked=n===checks.length && n>0;
  selectAll.indeterminate=n>0 && n<checks.length;
}
selectAll?.addEventListener('change', e=>{
  checks.forEach(c=>c.checked=e.target.checked);
  updateSelection();
});
function clearSelection(){ checks.forEach(c=>c.checked=false); updateSelection(); }
function printSelected(){
  const sel=[...checks].filter(c=>c.checked).map(c=>({code:c.value, label:c.dataset.label}));
  if(!sel.length) return;
  const w=window.open('','_blank');
  const html='<!doctype html><html><head><meta charset="utf-8"><title>Barcodes — '+sel.length+' selected</title><style>body{font-family:system-ui,sans-serif; margin:0; padding:16px} .grid{display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:14px} .card{border:1px solid #ddd; border-radius:12px; padding:12px; text-align:center; break-inside:avoid} .code{font-family:monospace; font-size:11px; font-weight:700; letter-spacing:.08em} img,svg{max-width:100%; height:60px} @media print{ body{padding:8px} }</style></head><body><h2 style="font-size:14px; margin:0 0 12px">Selected barcodes — '+sel.length+'</h2><div class="grid">'+sel.map(s=>'<div class="card"><div style="font-size:11px; color:#666; white-space:nowrap; overflow:hidden; text-overflow:ellipsis">'+escapeHtml(s.label)+'</div><svg class="jsb" data-value="'+escapeHtml(s.code)+'"></svg><div class="code">'+escapeHtml(s.code)+'</div></div>').join('')+'</div><script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"><\/script><script>document.querySelectorAll(".jsb").forEach(el=>{ try{ JsBarcode(el, el.dataset.value, {format:"CODE128", height:50, displayValue:false, margin:0}); }catch(e){} }); setTimeout(()=>window.print(), 600);<\/script></body></html>';
  w.document.write(html); w.document.close();
}
function escapeHtml(s){ return String(s).replace(/[&<>"]/g,c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
</script>
<style>@media print{ .sidebar,.topbar,.catalog-card:has(#selectAll){display:none!important} .main{margin-left:0!important} .page{padding:0} .catalog-card{break-inside:avoid} .barcode-card{break-inside:avoid} }</style>

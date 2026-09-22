<?php $title = $mode==='issue' ? 'Issue Book' : 'Return Book'; ?>
<div class="page-head">
  <div class="eyebrow">Circulation <i></i> <span class="mono"><?= $mode==='issue'?'Issue desk':'Return desk' ?></span></div>
  <h1><?= $mode==='issue' ? 'Issue <em>a copy</em>' : 'Return <em>a copy</em>' ?></h1>
  <p><?= $mode==='issue' ? 'Scan accession · verify borrower · staff: 90 days, students: 14 days. One accession = one book.' : 'Scan accession · holiday-aware fine (₹2/net day) · due-slip cleared. Staff have 90 days.' ?></p>
</div>

<div class="grid-2">
  <div class="catalog-card">
    <div class="perf-notch"></div>
    <div class="card-inner">
      <div class="eyebrow">Scan & verify <i></i></div>
      <form id="circForm" method="post" action="<?= $mode==='issue' ? '/issue' : '/return' ?>" style="display:grid; gap:12px; margin-top:12px">
        <div class="field"><label>Borrower — enrollment / library ID / scan</label><input class="input mono" data-barcode-input name="member_code" id="circBorrower" required placeholder="0901CS221043 or CS20028…" value="<?= e($_GET['member'] ?? '') ?>" autofocus></div>
        <div class="field"><label>Accession — book copy (e.g. 000029)</label><input class="input mono" data-barcode-input name="accession" id="circAccession" required placeholder="000029 — scan here" value="<?= e($_GET['book'] ?? '') ?>"></div>
        <?php if(!empty($error)): ?><div style="background:var(--vermillion-bg); border:1px solid var(--maroon-border); color:var(--maroon); padding:10px 12px; border-radius:12px; font-size:13px"><?= e($error) ?></div><?php endif; ?>
        <?php if(!empty($success)): ?><div style="background:var(--success-bg); border:1px solid #CFF0E3; color:var(--success); padding:10px 12px; border-radius:12px; font-size:13px"><?= e($success) ?></div><?php endif; ?>
        <label style="display:flex; gap:8px; align-items:center; font-size:13px; color:var(--ink-soft)"><input type="checkbox" name="barcode_scanned" value="1" checked> Barcode scanned</label>
        <button class="btn btn-primary" id="circSubmit" style="justify-content:center; padding:12px"><?= $mode==='issue' ? 'Stamp issue — 14d / 90d' : 'Stamp return' ?></button>
      </form>
      <p style="font-size:11px; color:var(--slate); margin-top:10px" class="mono">Due slip: students 14 days, staff 90 days. Net days exclude holidays. Max 3 books/borrower.</p>
    </div>
  </div>

  <div class="catalog-card" style="background:var(--paper-2)">
    <div class="card-inner">
      <div class="eyebrow">Due slip preview <i></i></div>
      <div style="margin-top:12px; background:white; border:1px solid var(--border); border-radius:14px; padding:14px; position:relative">
        <div style="position:absolute; right:12px; top:12px" class="stamp">DUE <?= date('d M Y', strtotime('+14 days')) ?></div>
        <div style="font-family:Fraunces; font-weight:800">RJIT Central Library</div>
        <div style="font-size:12px; color:var(--slate)">Accession: <span class="mono" style="color:var(--ink)"><?= e($_GET['book'] ?? '000029') ?></span> · Due: <b style="color:var(--ink)"><?= date('d/m/Y', strtotime('+14 days')) ?></b></div>
        <div style="margin-top:10px; border-top:1px dashed var(--border); padding-top:10px; font-size:11px; color:var(--slate)" class="mono">Fine: ₹2 per net overdue day (holidays excluded) · Please return at the desk.</div>
        <div style="margin-top:8px; display:flex; gap:6px"><span class="brass-hole"></span><span class="brass-hole" style="opacity:.6"></span><span class="brass-hole" style="opacity:.3"></span></div>
      </div>
      <?php if($mode==='issue'): ?>
      <div style="margin-top:14px; display:grid; gap:8px">
        <a href="/students" class="btn btn-ghost btn-small" style="justify-content:center">Browse borrowers →</a>
        <a href="/books" class="btn btn-ghost btn-small" style="justify-content:center">Browse catalog →</a>
      </div>
      <?php else: ?>
      <div style="margin-top:14px; display:flex; gap:8px; flex-wrap:wrap">
        <a href="/return" class="btn <?= empty($status)?'btn-primary':'btn-ghost' ?> btn-small">All</a>
        <a href="/return?status=issued" class="btn <?= $status==='issued'?'btn-primary':'btn-ghost' ?> btn-small">Issued</a>
        <a href="/return?status=overdue" class="btn <?= $status==='overdue'?'btn-primary':'btn-ghost' ?> btn-small">Overdue</a>
      </div>
      <form method="get" action="/return" class="search" style="margin-top:10px">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="search" value="<?= e($search ?? '') ?>" placeholder="Search title / accession / borrower">
        <?php if(!empty($status)): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if($mode==='return'): ?>
<div class="catalog-card" style="margin-top:16px">
  <div class="card-inner">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px">
      <div class="eyebrow">Issued books — ready to return <i></i> <span class="mono" style="color:var(--slate-2)"><?= (int)($total ?? 0) ?> active</span></div>
      <span class="mono" style="font-size:11px; color:var(--slate)">Page <?= (int)($page ?? 1) ?> of <?= (int)($pages ?? 1) ?></span>
    </div>
    <div class="table-wrap" style="margin-top:12px">
      <table class="data-table">
        <thead><tr><th>Book</th><th>Accession</th><th>Borrower</th><th>Due</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach(($issuedList ?? []) as $r): ?>
          <tr>
            <td><b style="font-size:13px"><?= e($r['title']) ?></b><br><span class="mono" style="font-size:11px; color:var(--slate)"><?= e($r['book_code']) ?></span></td>
            <td class="mono" style="font-weight:700; font-size:12px"><?= e($r['accession_no'] ?? '—') ?></td>
            <td><span style="font-size:13px; font-weight:600"><?= e($r['borrower_name'] ?? '—') ?></span><br><span class="mono" style="font-size:11px; color:var(--slate)"><?= e($r['borrower_id'] ?? '—') ?></span></td>
            <td class="mono" style="font-size:12px; color:<?= $r['status']==='overdue' ? 'var(--vermillion)' : 'var(--slate)' ?>"><?= e($r['due_date']) ?></td>
            <td><span class="pill <?= $r['status']==='overdue'?'pill-red':'pill-green' ?>"><span class="pill-dot"></span><?= e($r['status']) ?></span></td>
            <td>
              <button class="btn btn-primary btn-small" onclick="openReturnModal('<?= e($r['accession_no'] ?? '') ?>','<?= e($r['borrower_name'] ?? '') ?>','<?= e($r['borrower_id'] ?? '') ?>','<?= e(str_replace("'","\'",$r['title'])) ?>')">Return</button>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(empty($issuedList)): ?><tr><td colspan="6" style="text-align:center; color:var(--slate); padding:24px">No issued books — desk is clear.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php if(!empty($pages) && $pages>1):
      $qS=urlencode($search ?? ''); $qSt=urlencode($status ?? '');
      $base="/return?search=$qS".($qSt ? "&status=$qSt" : "")."&page=";
      $current=(int)($page??1); $delta=2; $range=[];
      for($i=1;$i<=$pages;$i++){ if($i==1||$i==$pages||($i>=$current-$delta&&$i<=$current+$delta)) $range[]=$i; elseif(end($range)!=='...') $range[]='...'; }
    ?>
    <div class="pagination" style="display:flex; gap:6px; justify-content:center; margin-top:14px; flex-wrap:wrap">
      <a href="<?= $current>1?$base.($current-1):'#' ?>" class="btn btn-ghost btn-small" style="<?= $current<=1?'opacity:.35;pointer-events:none':'' ?>">‹</a>
      <?php foreach($range as $p): if($p==='...'): ?><span style="width:36px; display:flex; align-items:center; justify-content:center; color:var(--slate)">…</span><?php else: ?><a href="<?= $base.$p ?>" class="btn <?= $p==$current?'btn-primary':'btn-ghost' ?> btn-small" style="min-width:36px; justify-content:center"><?= $p ?></a><?php endif; endforeach; ?>
      <a href="<?= $current<$pages?$base.($current+1):'#' ?>" class="btn btn-ghost btn-small" style="<?= $current>=$pages?'opacity:.35;pointer-events:none':'' ?>">›</a>
    </div>
    <?php endif; ?>
    <div style="margin-top:12px; border-top:1px dashed var(--border); padding-top:10px">
      <div class="eyebrow">Overdue spotlight <i></i></div>
      <div style="display:grid; gap:6px; margin-top:8px">
        <?php foreach(($overdue ?? []) as $o): ?><div style="display:flex; justify-content:space-between; font-size:12px; padding:6px 8px; background:var(--vermillion-bg); border:1px solid var(--maroon-border); border-radius:8px"><span><b><?= e($o['title']) ?></b> <span class="mono" style="font-size:11px">· <?= e($o['accession_no'] ?? '') ?></span> — <?= e($o['name']) ?></span><span class="mono" style="color:var(--vermillion)"><?= e($o['due_date']) ?></span></div><?php endforeach; if(empty($overdue)): ?><div style="font-size:12px; color:var(--slate)">No overdue — all slips on time.</div><?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Return verification modal -->
<div id="returnModal" class="modal-overlay" style="display:none">
  <div class="modal" onclick="event.stopPropagation()" style="max-width:460px">
    <div class="modal-header">
      <h2>Confirm return — scan borrower</h2>
      <button class="btn btn-ghost btn-icon" onclick="closeReturnModal()" aria-label="Close"><svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
    </div>
    <div class="modal-body">
      <p style="font-size:13px; color:var(--slate); margin:0">Scan the borrower's library card / enrollment barcode. Must match the borrower who has this book.</p>
      <div style="margin-top:12px; background:var(--paper-2); border:1px solid var(--border); border-radius:12px; padding:12px">
        <div style="font-size:11px; letter-spacing:.12em; text-transform:uppercase; color:var(--slate); font-weight:700">Expected borrower</div>
        <div id="rmBorrower" style="font-weight:800; font-size:15px; margin-top:4px"></div>
        <div id="rmBorrowerId" class="mono" style="font-size:11px; color:var(--slate)"></div>
        <div style="margin-top:8px; font-size:11px; letter-spacing:.12em; text-transform:uppercase; color:var(--slate); font-weight:700">Book</div>
        <div id="rmBook" style="font-size:13px; font-weight:600"></div>
        <div id="rmAcc" class="mono" style="font-size:11px; color:var(--slate)"></div>
      </div>
      <div class="field" style="margin-top:12px"><label>Scan borrower barcode <span style="color:var(--vermillion)">*</span></label><input class="input mono" id="rmScan" data-barcode-input placeholder="Scan enrollment / library ID" autocomplete="off"></div>
      <div id="rmError" style="display:none; margin-top:8px; background:var(--vermillion-bg); border:1px solid var(--maroon-border); color:var(--maroon); padding:10px 12px; border-radius:10px; font-size:13px"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeReturnModal()">Cancel</button>
      <button class="btn btn-primary" onclick="confirmReturn()">Verify &amp; return</button>
    </div>
  </div>
</div>

<form id="hiddenReturnForm" method="post" action="/return" style="display:none">
  <input type="hidden" name="accession" id="hrAcc">
  <input type="hidden" name="member_code" id="hrMember">
  <input type="hidden" name="barcode_scanned" value="1">
</form>

<script>
let pendingAcc='', expectedId='';
function openReturnModal(acc, borrowerName, borrowerId, title){
  pendingAcc=acc; expectedId=borrowerId;
  document.getElementById('rmBorrower').textContent=borrowerName;
  document.getElementById('rmBorrowerId').textContent=borrowerId;
  document.getElementById('rmBook').textContent=title;
  document.getElementById('rmAcc').textContent='ACC '+acc;
  document.getElementById('rmScan').value='';
  document.getElementById('rmError').style.display='none';
  document.getElementById('returnModal').style.display='flex';
  setTimeout(()=>document.getElementById('rmScan').focus(),80);
}
function closeReturnModal(){ document.getElementById('returnModal').style.display='none'; }
document.getElementById('returnModal').addEventListener('click', closeReturnModal);
document.getElementById('rmScan').addEventListener('keydown', e=>{
  if(e.key==='Enter'){ e.preventDefault(); confirmReturn(); }
});
function confirmReturn(){
  const scanned=(document.getElementById('rmScan').value||'').trim();
  if(!scanned){ showRmError('Please scan borrower barcode'); return; }
  // normalize: compare lowercased, stripped leading zeros handled server-side too
  if(scanned.toLowerCase() !== expectedId.toLowerCase()){
    // allow barcode_data same as enrollment/library — server will double-check, but warn here
    showRmError('Scanned “'+scanned+'” does not match expected borrower “'+expectedId+'”. Please scan the correct card for '+document.getElementById('rmBorrower').textContent+'.');
    return;
  }
  document.getElementById('hrAcc').value=pendingAcc;
  document.getElementById('hrMember').value=scanned;
  document.getElementById('hiddenReturnForm').submit();
}
function showRmError(msg){
  const el=document.getElementById('rmError');
  el.textContent=msg; el.style.display='block';
}
// Quick-return top form → also via modal
(function(){
  const form=document.getElementById('circForm');
  if(!form) return;
  const isReturn = window.location.pathname === '/return';
  if(!isReturn) return;
  form.addEventListener('submit', e=>{
    // if already via modal, let through
    if(form.dataset.modalVerified==='1'){ form.dataset.modalVerified=''; return; }
    e.preventDefault();
    const acc=document.getElementById('circAccession').value.trim();
    const borrower=document.getElementById('circBorrower').value.trim();
    if(!acc){ alert('Accession required'); return; }
    // If borrower field already filled, use it as expected and still require rescan via modal for verification
    // Lookup expected borrower from server? For quick form we don't know expected yet — ask to rescan borrower
    // Show modal with entered borrower as expected, force rescan
    if(!borrower){ alert('Borrower ID required — enter enrollment/library ID then scan to verify'); return; }
    openReturnModal(acc, borrower, borrower, 'Acc '+acc);
    // Pre-fill scan input empty to force fresh scan
    document.getElementById('rmScan').placeholder='Rescan '+borrower+' to confirm';
  });
})();
</script>
<?php endif; ?>

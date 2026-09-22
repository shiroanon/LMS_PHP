<?php $title = $s['name'] ?? 'Borrower'; ?>
<div class="page-head">
  <a href="/students" class="btn btn-ghost btn-small">← Borrower ledger</a>
  <div style="display:flex; gap:16px; align-items:center; margin-top:12px">
    <div class="avatar" style="width:56px; height:56px; font-size:22px; background:var(--paper-2); border:1px solid var(--border)">◎</div>
    <div>
      <h1 style="margin:0; font-size:26px" class="display"><?= e($s['name']) ?> <span class="mono" style="font-size:13px; color:var(--slate); font-weight:500">· <?= e($s['enrollment_no']) ?></span></h1>
      <div><span class="pill pill-slate"><?= e($s['branch']) ?> · <?= (int)$s['year'] ?> yr</span> <span class="pill <?= $s['status']==='active'?'pill-green':'pill-amber' ?>"><span class="pill-dot"></span><?= e($s['status']) ?></span> <?php if(empty($s['library_id'])): ?><span class="pill pill-red"><span class="pill-dot"></span>No library ID</span><?php endif; ?> <span class="mono" style="font-size:11px; color:var(--slate)">ID <?= e($s['library_id'] ?? '—') ?></span></div>
    </div>
  </div>
</div>

<div style="display:flex; gap:8px; margin-bottom:14px; flex-wrap:wrap">
  <button class="btn btn-primary btn-small" onclick="const p=document.getElementById('editPanel'); p.style.display=p.style.display==='none'?'':'none'">Edit borrower</button>
  <form method="post" action="/students/<?= (int)$s['id'] ?>/delete" onsubmit="return confirm('Remove <?= e($s['name']) ?>? Linked loans, fines, visits and the login go with it.')" style="margin:0"><button class="btn btn-ghost btn-small" style="color:var(--vermillion)">Delete</button></form>
</div>
<div id="editPanel" class="catalog-card" style="display:<?= isset($_GET['edit']) ? '' : 'none' ?>; margin-bottom:16px">
  <div class="card-inner">
    <div class="eyebrow">Edit — enrollment is the login <i></i> not editable</div>
    <form method="post" action="/students/<?= (int)$s['id'] ?>" style="display:grid; gap:12px; margin-top:12px">
      <div class="form-row"><div class="field"><label>Enrollment (login)</label><input class="input mono" value="<?= e($s['enrollment_no']) ?>" disabled></div><div class="field"><label>Library ID</label><input class="input mono" name="library_id" value="<?= e($s['library_id'] ?? '') ?>" placeholder="leave blank if none yet"></div></div>
      <div class="field"><label>Name *</label><input class="input" name="name" value="<?= e($s['name']) ?>" required></div>
      <div class="form-row"><div class="field"><label>Branch *</label><input class="input" name="branch" value="<?= e($s['branch']) ?>" required></div><div class="field"><label>Year</label><select name="year" class="select"><?php for($y=1;$y<=8;$y++): ?><option value="<?= $y ?>" <?= (int)$s['year']===$y?'selected':'' ?>><?= $y ?> yr</option><?php endfor; ?></select></div></div>
      <div class="form-row"><div class="field"><label>Contact</label><input class="input" name="contact" value="<?= e($s['contact'] ?? '') ?>"></div><div class="field"><label>Email</label><input class="input" type="email" name="email" value="<?= e($s['email'] ?? '') ?>"></div></div>
      <div class="field"><label>Status</label><select name="status" class="select"><?php foreach(['active','passed_out','inactive'] as $st): ?><option value="<?= $st ?>" <?= $s['status']===$st?'selected':'' ?>><?= $st ?></option><?php endforeach; ?></select></div>
      <div style="display:flex; gap:8px; justify-content:flex-end">
        <a href="/students/<?= (int)$s['id'] ?>" class="btn btn-ghost">Cancel</a>
        <button class="btn btn-primary">Save changes</button>
      </div>
    </form>
  </div>
</div>

<div class="catalog-card" style="margin-bottom:16px">
  <div class="card-inner">
    <div class="eyebrow">Borrower record <i></i> <span class="mono">on file since <?= e(substr($s['created_at'] ?? '', 0, 10) ?: '—') ?></span></div>
    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:10px; margin-top:10px; font-size:13px">
      <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">ENROLLMENT</div><div class="mono" style="font-weight:700"><?= e($s['enrollment_no']) ?></div></div>
      <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">LIBRARY ID</div><div class="mono" style="font-weight:700"><?php if(empty($s['library_id'])): ?><span class="pill pill-red"><span class="pill-dot"></span>No library ID</span><?php else: ?><?= e($s['library_id']) ?><?php endif; ?></div></div>
      <?php if(!empty($s['barcode_data']) && $s['barcode_data'] !== ($s['library_id'] ?? null)): ?><div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">BARCODE</div><div class="mono"><?= e($s['barcode_data']) ?></div></div><?php endif; ?>
      <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">STATUS</div><div><span class="pill <?= $s['status']==='active'?'pill-green':($s['status']==='passed_out'?'pill-amber':'pill-red') ?>"><span class="pill-dot"></span><?= e($s['status']) ?></span></div></div>
      <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">BRANCH</div><div><?= e($s['branch']) ?></div></div>
      <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">YEAR</div><div class="mono"><?= (int)$s['year'] ?> yr</div></div>
      <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">CONTACT</div><div class="mono"><?= e($s['contact'] ?? '—') ?></div></div>
      <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">EMAIL</div><div style="word-break:break-all"><?= e($s['email'] ?? '—') ?></div></div>
      <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">FATHER'S NAME</div><div><?= e($s['father_name'] ?? '—') ?></div></div>
      <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">GENDER</div><div><?= e($s['gender'] ?? '—') ?></div></div>
      <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">DATE OF BIRTH</div><div class="mono"><?= e($s['dob'] ?? '—') ?></div></div>
      <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">ADMITTED</div><div class="mono"><?= e($s['admission_date'] ?? '—') ?></div></div>
      <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">LAST GATE VISIT</div><div class="mono"><?= e($s['last_library_visit'] ?? '—') ?></div></div>
      <?php if(!empty($s['address'])): ?><div style="grid-column:1/-1"><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">ADDRESS</div><div><?= e($s['address']) ?></div></div><?php endif; ?>
    </div>
  </div>
</div>

<div class="grid-2">
  <div class="catalog-card"><div class="card-inner"><div class="eyebrow">Active loans <i></i></div><div class="table-wrap" style="margin-top:12px"><table class="data-table"><thead><tr><th>Book</th><th>Accession</th><th>Due</th><th>Status</th></tr></thead><tbody><?php foreach($issues as $r): ?><tr><td><b><?= e($r['title']) ?></b></td><td class="mono" style="font-size:11px"><?= e($r['accession_no'] ?? '') ?></td><td class="mono" style="font-size:12px"><?= e($r['due_date']) ?></td><td><span class="pill <?= $r['status']==='overdue'?'pill-red':($r['status']==='issued'?'pill-green':'pill-slate') ?>"><span class="pill-dot"></span><?= e($r['status']) ?></span></td></tr><?php endforeach; if(empty($issues)): ?><tr><td colspan="4" style="color:var(--slate); text-align:center">No loans.</td></tr><?php endif; ?></tbody></table></div></div></div>
  <div style="display:grid; gap:16px">
    <div class="catalog-card"><div class="card-inner"><div class="eyebrow">Fines <i></i></div><?php foreach($fines as $f): ?><div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--border); font-size:13px"><span><?= e($f['title']) ?> <span class="mono" style="font-size:11px; color:var(--slate)">₹<?= e($f['amount']) ?></span></span><span class="pill <?= $f['status']==='paid'?'pill-green':'pill-red' ?>"><?= e($f['status']) ?></span></div><?php endforeach; if(empty($fines)): ?><div style="color:var(--slate); font-size:13px; padding:10px 0">No fines.</div><?php endif; ?></div></div>
    <div class="catalog-card"><div class="card-inner"><div class="eyebrow">Gate <i></i></div><?php foreach($visits as $v): ?><div style="font-size:12px; padding:6px 0; border-bottom:1px solid var(--border)" class="mono"><?= e($v['check_in_time']) ?> → <?= e($v['check_out_time'] ?? 'inside') ?></div><?php endforeach; if(empty($visits)): ?><div style="color:var(--slate); font-size:13px">No visits.</div><?php endif; ?></div></div>
  </div>
</div>

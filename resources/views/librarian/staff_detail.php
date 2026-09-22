<?php $title=$s['name']; ?>
<div class="page-head">
  <a href="/staff" class="btn btn-ghost btn-small">← Staff ledger</a>
  <div style="display:flex; gap:16px; align-items:center; margin-top:12px; flex-wrap:wrap">
    <img src="/assets/img/logo.png" alt="" style="width:48px;height:48px;object-fit:contain;background:white;border-radius:10px;padding:4px;border:1px solid var(--border)">
    <div>
      <h1 style="margin:0" class="display"><?= e($s['name']) ?> <span class="mono" style="font-size:13px; color:var(--slate); font-weight:500">· <?= e($s['library_id']) ?></span></h1>
      <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:6px">
        <span class="pill pill-slate"><?= e($s['loan_category'] ?: '—') ?></span>
        <span class="pill pill-slate"><?= e($s['membership_type'] ?: '—') ?></span>
        <span class="pill <?= ($s['status'] ?? 'active')==='active'?'pill-green':'pill-red' ?>"><span class="pill-dot"></span><?= e($s['status'] ?? 'active') ?></span>
        <span class="mono" style="font-size:11px; color:var(--slate)">Active loans <?= (int)$active ?> · Total <?= count($issues) ?></span>
      </div>
    </div>
    <form method="post" action="/staff/<?= (int)$s['id'] ?>/delete" onsubmit="return confirm('Delete staff record?')" style="margin-left:auto"><button class="btn btn-ghost btn-small" style="color:var(--vermillion)">Delete</button></form>
  </div>
</div>

<div class="catalog-card" style="margin-bottom:16px">
  <div class="card-inner">
    <div class="eyebrow">Staff record <i></i> <span class="mono"><?= e($s['library_id'] ?? '—') ?></span></div>
    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:10px; margin-top:10px; font-size:13px">
      <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">NAME</div><div style="font-weight:700"><?= e($s['name']) ?></div></div>
      <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">STAFF ID</div><div class="mono" style="font-weight:700"><?= e($s['library_id'] ?? '—') ?></div></div>
      <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">DEPARTMENT</div><div><?= e($s['loan_category'] ?: '—') ?></div></div>
      <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">MEMBERSHIP</div><div><?= e($s['membership_type'] ?: '—') ?></div></div>
      <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">START DATE</div><div class="mono"><?= e($s['start_date'] ?: '—') ?></div></div>
      <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">STATUS</div><div><span class="pill <?= ($s['status'] ?? 'active')==='active'?'pill-green':'pill-red' ?>"><span class="pill-dot"></span><?= e($s['status'] ?? 'active') ?></span></div></div>
      <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">DATE ADDED</div><div class="mono"><?= e($s['date_added'] ?: '—') ?></div></div>
      <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">LOANS</div><div class="mono"><b><?= (int)$active ?></b> active · <?= count($issues) ?> shown</div></div>
      <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">FINES PENDING</div><div class="mono" style="font-weight:700; color:<?= (float)($sum['pending']??0)>0?'var(--vermillion)':'var(--success)' ?>">₹<?= number_format((float)($sum['pending']??0)) ?></div></div>
    </div>
  </div>
</div>

<div class="grid-2">
  <div style="display:grid; gap:14px">
    <div class="catalog-card"><div class="card-inner">
      <div class="eyebrow">Edit <i></i></div>
      <form method="post" action="/staff/<?= (int)$s['id'] ?>" style="display:grid; gap:10px; margin-top:10px">
        <div class="field"><label>Name</label><input class="input" name="name" value="<?= e($s['name']) ?>" required></div>
        <div class="field"><label>Staff ID</label><input class="input mono" name="library_id" value="<?= e($s['library_id']) ?>"></div>
        <div class="form-row"><div class="field"><label>Department</label><input class="input" name="loan_category" value="<?= e($s['loan_category']) ?>"></div><div class="field"><label>Membership type</label><input class="input" name="membership_type" value="<?= e($s['membership_type']) ?>"></div></div>
        <div class="field"><label>Status</label><select name="status" class="select"><option value="active" <?= ($s['status'] ?? 'active')==='active'?'selected':'' ?>>Active</option><option value="inactive" <?= ($s['status'] ?? '')==='inactive'?'selected':'' ?>>Inactive</option></select></div>
        <button class="btn btn-primary">Save changes</button>
      </form>
    </div></div>
    <div class="catalog-card"><div class="card-inner">
      <div class="eyebrow">Fines <i></i> <span class="pill <?= (float)($sum['pending']??0)>0?'pill-red':'pill-green' ?>">₹<?= number_format((float)($sum['pending']??0)) ?> pending</span></div>
      <div style="margin-top:10px; display:grid; gap:8px">
        <?php foreach($fines as $f): ?><div style="display:flex; justify-content:space-between; padding:8px 10px; background:var(--paper-2); border:1px solid var(--border); border-radius:10px; font-size:13px"><span><?= e($f['title']) ?> <span class="mono" style="font-size:11px">₹<?= e($f['amount']) ?></span></span><span class="pill <?= $f['status']==='paid'?'pill-green':'pill-red' ?>"><?= e($f['status']) ?></span></div><?php endforeach; if(empty($fines)): ?><div style="color:var(--slate); font-size:13px; text-align:center; padding:14px">No fines.</div><?php endif; ?>
      </div>
    </div></div>
  </div>
  <div class="catalog-card"><div class="card-inner">
    <div class="eyebrow">Loans <i></i> <?= count($issues) ?> records</div>
    <div class="table-wrap" style="margin-top:12px; max-height:560px; overflow:auto">
      <table class="data-table"><thead><tr><th>Book</th><th>Due</th><th>Status</th></tr></thead><tbody>
      <?php foreach($issues as $r): ?><tr><td><b style="font-size:13px"><?= e($r['title']) ?></b><br><span class="mono" style="font-size:11px; color:var(--slate)"><?= e($r['accession_no'] ?? $r['book_code']) ?></span></td><td class="mono" style="font-size:12px"><?= e($r['due_date']) ?></td><td><span class="pill <?= $r['status']==='overdue'?'pill-red':($r['status']==='issued'?'pill-green':'pill-slate') ?>"><?= e($r['status']) ?></span></td></tr><?php endforeach; if(empty($issues)): ?><tr><td colspan="3" style="text-align:center; color:var(--slate)">No issues.</td></tr><?php endif; ?>
      </tbody></table>
    </div>
    <div style="display:flex; gap:8px; margin-top:12px"><a href="/issue?member=<?= e($s['library_id']) ?>" class="btn btn-primary btn-small">Issue to this staff</a><a href="/staff/new" class="btn btn-ghost btn-small">Add another</a></div>
  </div></div>
</div>

<?php $title='Profile'; ?>
<div class="page-head"><div class="eyebrow">Borrower <i></i> <span class="mono"><?= e($s['enrollment_no']) ?></span></div><h1><?= e($s['name']) ?></h1><p><?= e($s['branch']) ?> · Year <?= (int)$s['year'] ?> · <span class="pill <?= $s['status']==='active'?'pill-green':'pill-amber' ?>"><?= e($s['status']) ?></span></p></div>
<?php if((int)($user['must_change_password'] ?? 0) === 1): ?><div style="background:var(--warning-bg); border:1px solid #FBE8B5; color:var(--warning); padding:12px 14px; border-radius:12px; font-size:13px; font-weight:600; margin-bottom:16px">First login — set a new password below to unlock your shelf.</div><?php endif; ?>
<div class="grid-2">
  <div class="catalog-card"><div class="card-inner">
    <div class="eyebrow">Contact <i></i></div>
    <form method="post" action="/profile" style="display:grid; gap:12px; margin-top:12px">
      <div class="form-row"><div class="field"><label>Contact</label><input class="input" name="contact" value="<?= e($s['contact'] ?? '') ?>"></div><div class="field"><label>Email</label><input class="input" name="email" value="<?= e($s['email'] ?? '') ?>"></div></div>
      <div style="height:1px; background:var(--border); margin:8px 0"></div>
      <div class="eyebrow">Change password <i></i></div>
      <div class="field"><label>Current (skip if first login)</label><input type="password" name="current_password" class="input"></div>
      <div class="field"><label>New password</label><input type="password" name="new_password" class="input"></div>
      <button class="btn btn-primary" style="justify-content:center">Save changes</button>
    </form>
  </div></div>
  <div class="catalog-card"><div class="card-inner"><div class="eyebrow">My loans <i></i></div><div class="table-wrap" style="margin-top:12px"><table class="data-table"><thead><tr><th>Book</th><th>Due</th><th>Status</th></tr></thead><tbody><?php foreach($issues as $r): ?><tr><td><?= e($r['title']) ?></td><td class="mono" style="font-size:12px"><?= e($r['due_date']) ?></td><td><span class="pill <?= $r['status']==='overdue'?'pill-red':($r['status']==='issued'?'pill-green':'pill-slate') ?>"><?= e($r['status']) ?></span></td></tr><?php endforeach; if(empty($issues)): ?><tr><td colspan="3" style="color:var(--slate); text-align:center">No loans.</td></tr><?php endif; ?></tbody></table></div></div></div>
</div>

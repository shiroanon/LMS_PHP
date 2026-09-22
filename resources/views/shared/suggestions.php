<?php $title='Suggestions'; ?>
<div class="page-head"><div class="eyebrow">Requests <i></i></div><h1>Suggestions</h1><p>What should the library buy next? Students ask, librarian stamps.</p></div>
<div class="grid-2">
  <div class="catalog-card"><div class="card-inner">
    <div class="eyebrow">Suggest <i></i></div>
    <?php if(($user['role'] ?? '')==='student'): ?>
    <form method="post" action="/suggestions" style="display:grid; gap:12px; margin-top:12px">
      <div class="field"><label>Title</label><input class="input" name="title" required></div>
      <div class="form-row"><div class="field"><label>Type</label><select name="type" class="select"><option value="book">Book</option><option value="magazine">Magazine</option><option value="newspaper">Newspaper</option><option value="infrastructure">Infrastructure</option><option value="other">Other</option></select></div><div class="field"><label>Author (if book)</label><input class="input" name="author"></div></div>
      <div class="field"><label>Description</label><textarea class="textarea" name="description"></textarea></div>
      <button class="btn btn-primary">Send to desk</button>
    </form>
    <?php else: ?><p style="color:var(--slate); font-size:13px">Students submit; you approve.</p><?php endif; ?>
  </div></div>
  <div class="catalog-card"><div class="card-inner"><div class="eyebrow">Queue <i></i></div><div style="display:grid; gap:10px; margin-top:12px"><?php foreach(($suggestions ?? []) as $s): ?><div style="padding:12px; border:1px solid var(--border); border-radius:12px; background:white"><div style="display:flex; justify-content:space-between"><b style="font-size:13px"><?= e($s['title']) ?></b><span class="pill <?= $s['status']==='approved'?'pill-green':($s['status']==='rejected'?'pill-red':'pill-amber') ?>"><span class="pill-dot"></span><?= e($s['status']) ?></span></div><div style="font-size:12px; color:var(--slate)"><?= e($s['type']) ?> · <?= e($s['name'] ?? '') ?></div><?php if(($user['role'] ?? '')==='librarian' && $s['status']==='pending'): ?><form method="post" action="/suggestions/<?= (int)$s['id'] ?>/status" style="display:flex; gap:6px; margin-top:8px"><button name="status" value="approved" class="btn btn-primary btn-small">Approve</button><button name="status" value="rejected" class="btn btn-ghost btn-small">Reject</button></form><?php endif; ?></div><?php endforeach; if(empty($suggestions)): ?><div style="color:var(--slate); font-size:13px; text-align:center; padding:18px">No suggestions — the inbox is clear.</div><?php endif; ?></div></div></div>
</div>

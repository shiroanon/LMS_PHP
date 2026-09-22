<?php $title='Digital Library'; ?>
<div class="page-head">
  <div class="eyebrow">Archive <i></i> <span class="mono"><?= count($resources ?? []) ?> files</span></div>
  <h1>Digital <em>library</em></h1>
  <p>Newspapers · magazines · e-books — the paperless shelf.</p>
</div>
<?php if(($user['role'] ?? '')==='librarian'): ?>
<div class="catalog-card" style="margin-bottom:16px"><div class="card-inner"><form method="post" action="/digital-library" enctype="multipart/form-data" style="display:grid; gap:12px"><div class="form-row"><div class="field"><label>Title</label><input class="input" name="title" required></div><div class="field"><label>Category</label><select name="category" class="select"><option>newspaper</option><option>current_affairs</option><option>magazine</option><option>ebook</option></select></div></div><div class="field"><label>File (PDF)</label><input type="file" name="file" accept=".pdf" required></div><button class="btn btn-primary">Upload to shelf</button></form></div></div>
<?php endif; ?>
<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:12px">
  <?php foreach(($resources ?? []) as $r): ?>
  <div class="catalog-card"><div class="card-inner"><span class="pill pill-slate"><?= e($r['category']) ?></span><div style="font-weight:700; margin-top:8px"><?= e($r['title']) ?></div><div class="mono" style="font-size:11px; color:var(--slate)"><?= e($r['publish_date']) ?> · <?= number_format(($r['file_size']??0)/1024) ?> KB</div><a href="/digital-library/<?= (int)$r['id'] ?>/download" class="btn btn-ghost btn-small" style="margin-top:10px; width:100%; justify-content:center">Download</a></div></div>
  <?php endforeach; if(empty($resources)): ?><div class="catalog-card" style="grid-column:1/-1"><div class="card-inner empty"><div class="stamp">Empty archive</div><h3>No digital holdings yet</h3></div></div><?php endif; ?>
</div>

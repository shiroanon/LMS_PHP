<?php $title='Backup'; ?>
<div class="page-head" style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:end">
  <div><div class="eyebrow">System <i></i> mysqldump + uploads</div><h1>Backup</h1><p>Zip of <span class="mono">lms.sql + storage/uploads</span>. Stored in <span class="mono">storage/backups</span>.</p></div>
  <form method="post" action="/backup/now"><button class="btn btn-primary">Create backup now</button></form>
</div>
<div class="table-wrap"><table class="data-table"><thead><tr><th>File</th><th>Size</th><th>Created</th><th></th></tr></thead><tbody>
<?php foreach($files as $f): ?><tr><td class="mono" style="font-weight:700"><?= e($f['filename']) ?></td><td class="mono" style="font-size:12px"><?= number_format($f['size']/1024,1) ?> KB</td><td class="mono" style="font-size:12px"><?= e($f['createdAt']) ?></td><td style="display:flex; gap:6px; justify-content:flex-end"><a href="/backup/<?= urlencode($f['filename']) ?>/download" class="btn btn-ghost btn-small">Download</a><form method="post" action="/backup/<?= urlencode($f['filename']) ?>/delete" onsubmit="return confirm('Delete backup?')"><button class="btn btn-ghost btn-small" style="color:var(--vermillion)">Delete</button></form></td></tr><?php endforeach; if(empty($files)): ?><tr><td colspan="4" style="text-align:center; color:var(--slate)">No backups yet — hit Create now.</td></tr><?php endif; ?>
</tbody></table></div>

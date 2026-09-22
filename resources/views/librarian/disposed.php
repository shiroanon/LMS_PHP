<?php $title='Disposed'; ?>
<div class="page-head"><div class="eyebrow">Archive <i></i> <span class="mono"><?= (int)$total ?> disposed</span></div><h1>Disposed <em>books</em></h1><p>Legacy accession register — kept for audit.</p></div>
<form method="get" action="/disposed" class="search" style="max-width:360px; margin-bottom:14px">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
  <input type="text" name="search" value="<?= e($_GET['search'] ?? '') ?>" placeholder="Accession / title">
</form>
<div class="table-wrap"><table class="data-table"><thead><tr><th>Accession</th><th>Title</th><th>ISBN</th><th>Publisher</th><th>Price</th><th>Status</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td class="mono" style="font-weight:700"><?= e($r['accession_no']) ?></td><td style="max-width:280px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis"><?= e($r['title']) ?></td><td class="mono" style="font-size:11px"><?= e($r['isbn'] ?: '—') ?></td><td><?= e($r['publisher'] ?: '—') ?></td><td class="mono" style="font-size:12px"><?= e($r['price'] ?: '—') ?></td><td><span class="pill pill-amber"><?= e($r['status']) ?></span></td></tr><?php endforeach; if(empty($rows)): ?><tr><td colspan="6" style="text-align:center; color:var(--slate)">No disposed records match.</td></tr><?php endif; ?>
</tbody></table></div>

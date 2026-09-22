<?php $title='Feedback'; ?>
<div class="page-head"><div class="eyebrow">Quality <i></i> <?= (int)($stats['cnt'] ?? 0) ?> reviews · avg <?= number_format((float)($stats['avg'] ?? 0),1) ?>/5</div><h1>Feedback</h1><p>Book ratings from borrowers.</p></div>
<?php if(empty($rows)): ?><div class="catalog-card"><div class="card-inner empty"><div class="stamp">No feedback</div><h3>Quiet shelf</h3><p>No ratings yet.</p></div></div>
<?php else: ?>
<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:12px">
  <?php foreach($rows as $r): ?>
  <div class="catalog-card"><div class="card-inner">
    <div style="display:flex; justify-content:space-between"><span class="mono" style="font-size:11px; color:var(--slate)"><?= e($r['enrollment_no']) ?> · <?= e($r['name']) ?></span><span class="pill pill-amber"><?= str_repeat('★', (int)$r['rating']) ?><?= str_repeat('☆', 5-(int)$r['rating']) ?></span></div>
    <div style="font-weight:700; margin-top:8px"><?= e($r['title'] ?? 'General') ?></div>
    <div style="font-size:13px; color:var(--slate); margin-top:4px"><?= e($r['comments'] ?: '—') ?></div>
    <div class="mono" style="font-size:11px; color:var(--slate); margin-top:8px"><?= e($r['created_at']) ?></div>
  </div></div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

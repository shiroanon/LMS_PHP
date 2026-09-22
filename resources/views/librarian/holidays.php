<?php $title='Holidays'; ?>
<div class="page-head"><div class="eyebrow">Calendar <i></i> <?= count($holidays) ?> holidays</div><h1>Holiday <em>calendar</em></h1><p>Excluded from net overdue days — keeps fines fair.</p></div>
<div class="grid-2">
  <div class="catalog-card"><div class="card-inner"><div class="eyebrow">Add holiday <i></i></div>
    <form method="post" action="/holidays" style="display:grid; gap:10px; margin-top:10px">
      <div class="field"><label>Date</label><input class="input" type="date" name="holiday_date" required></div>
      <div class="field"><label>Description</label><input class="input" name="description" placeholder="Diwali, Holi..."></div>
      <button class="btn btn-primary">Add to calendar</button>
    </form>
  </div></div>
  <div class="table-wrap" style="align-self:start"><table class="data-table"><thead><tr><th>Date</th><th>Description</th><th></th></tr></thead><tbody>
    <?php foreach($holidays as $h): ?><tr><td class="mono" style="font-weight:700"><?= e($h['holiday_date']) ?></td><td><?= e($h['description'] ?: '—') ?></td><td><form method="post" action="/holidays/<?= (int)$h['id'] ?>/delete"><button class="btn btn-ghost btn-small" style="color:var(--vermillion)" onclick="return confirm('Remove?')">✕</button></form></td></tr><?php endforeach; if(empty($holidays)): ?><tr><td colspan="3" style="text-align:center; color:var(--slate)">No holidays — add exam breaks.</td></tr><?php endif; ?>
  </tbody></table></div>
</div>

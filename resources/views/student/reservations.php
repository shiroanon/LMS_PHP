<?php $title='Reservations'; ?>
<div class="page-head"><div class="eyebrow">Queue <i></i></div><h1>Reservations</h1><p>Hold a title — you’ll be notified when a copy returns.</p>
<form method="post" action="/reservations/create" style="margin-top:12px; display:flex; gap:8px"><input class="input mono" name="book_id" placeholder="Book ID (e.g. 12)"><button class="btn btn-primary btn-small">Reserve</button></form>
</div>
<div class="table-wrap"><table class="data-table"><thead><tr><th>Book</th><th>Status</th><th>Reserved</th><th>Expires</th></tr></thead><tbody>
<?php foreach(($rows ?? []) as $r): ?><tr><td><?= e($r['title']) ?></td><td><span class="pill <?= $r['status']==='notified'?'pill-green':($r['status']==='pending'?'pill-amber':'pill-slate') ?>"><?= e($r['status']) ?></span></td><td class="mono" style="font-size:12px"><?= e($r['reservation_date']) ?></td><td class="mono" style="font-size:12px"><?= e($r['expires_at'] ?? '—') ?></td></tr><?php endforeach; if(empty($rows)): ?><tr><td colspan="4" style="text-align:center; color:var(--slate)">No reservations.</td></tr><?php endif; ?>
</tbody></table></div>

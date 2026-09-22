<?php $title='Fines'; ?>
<div class="page-head" style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:end">
  <div>
    <div class="eyebrow">Revenue <i></i> <span class="mono">₹2 / net day</span></div>
    <h1>Fines & <em>dues</em></h1>
    <p>Holiday-aware net days. Pay at desk, stamp cleared.</p>
  </div>
  <div style="display:flex; gap:8px"><a href="/fines?status=pending" class="btn <?= (($_GET['status'] ?? 'pending')==='pending'?'btn-primary':'btn-ghost') ?> btn-small">Pending</a><a href="/fines?status=paid" class="btn <?= (($_GET['status'] ?? '')==='paid'?'btn-primary':'btn-ghost') ?> btn-small">Paid</a></div>
</div>

<div class="grid-stats">
  <div class="slip"><div class="slip-top"><span class="slip-label">Pending</span><span class="slip-icon" style="background:var(--vermillion-bg)">₹</span></div><div class="slip-value">₹<?= number_format($sum_pending ?? 0) ?></div><div class="slip-sub"><?= (int)($count_pending ?? 0) ?> fines</div></div>
  <div class="slip"><div class="slip-top"><span class="slip-label">Collected</span><span class="slip-icon" style="background:var(--success-bg)">✓</span></div><div class="slip-value">₹<?= number_format($sum_paid ?? 0) ?></div><div class="slip-sub">All time</div></div>
  <div class="slip"><div class="slip-top"><span class="slip-label">Overdue copies</span><span class="slip-icon">◷</span></div><div class="slip-value"><?= (int)($overdue_count ?? 0) ?></div><div class="slip-sub">Needs sweep</div></div>
  <div class="slip"><div class="slip-top"><span class="slip-label">Rate</span><span class="slip-icon" style="background:var(--brass-muted)">₹</span></div><div class="slip-value">₹2</div><div class="slip-sub">per net day</div></div>
</div>

<div class="table-wrap">
  <table class="data-table">
    <thead><tr><th>Borrower</th><th>Book / Accession</th><th>Amount</th><th>Status</th><th>Due → Returned</th><th></th></tr></thead>
    <tbody>
    <?php foreach(($fines ?? []) as $f): ?>
      <tr>
        <td><b><?= e($f['name']) ?></b><br><span class="mono" style="font-size:11px; color:var(--slate)"><?= e($f['enrollment_no']) ?></span></td>
        <td><?= e($f['title']) ?><br><span class="mono" style="font-size:11px; color:var(--slate)"><?= e($f['accession_no'] ?? $f['book_id']) ?></span></td>
        <td class="mono" style="font-weight:700">₹<?= number_format($f['amount']) ?></td>
        <td><span class="pill <?= $f['status']==='paid'?'pill-green':'pill-red' ?>"><span class="pill-dot"></span><?= e($f['status']) ?></span></td>
        <td class="mono" style="font-size:11px"><?= e($f['due_date']) ?> → <?= e($f['return_date'] ?? '—') ?></td>
        <td><?php if($f['status']==='pending'): ?><form method="post" action="/fines/<?= (int)$f['id'] ?>/pay"><button class="btn btn-primary btn-small">Mark paid</button></form><?php else: ?><span style="color:var(--success); font-size:12px; font-weight:700">✓ Cleared</span><?php endif; ?></td>
      </tr>
    <?php endforeach; if(empty($fines)): ?><tr><td colspan="6" style="text-align:center; color:var(--slate); padding:24px">No fines in this pocket.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php $title='Dashboard'; ?>
<div class="page-head">
  <div class="eyebrow">Your shelf <i></i> <span class="mono" style="color:var(--slate)"><?= e($user['username'] ?? '') ?></span></div>
  <h1>Hello, <em><?= e($user['profile']['name'] ?? $user['display_name'] ?? $user['username']) ?></em></h1>
  <p>Track your loans, reserves, and the catalog — paper-light.</p>
</div>

<div class="grid-stats">
  <div class="slip"><div class="slip-top"><span class="slip-label">On loan</span><span class="slip-icon">📖</span></div><div class="slip-value"><?= (int)($my_active ?? 0) ?></div><div class="slip-sub">Due within <?= (int)($loan_days ?? 14) ?> days</div></div>
  <div class="slip"><div class="slip-top"><span class="slip-label">Overdue</span><span class="slip-icon" style="background:var(--vermillion-bg)">◷</span></div><div class="slip-value" style="color:var(--vermillion)"><?= (int)($my_overdue ?? 0) ?></div><div class="slip-sub">Return to stop fines</div></div>
  <div class="slip"><div class="slip-top"><span class="slip-label">Pending fines</span><span class="slip-icon" style="background:var(--brass-muted)">₹</span></div><div class="slip-value">₹<?= number_format($my_fines ?? 0) ?></div><div class="slip-sub">Net days only</div></div>
  <div class="slip"><div class="slip-top"><span class="slip-label">Catalog</span><span class="slip-icon">▦</span></div><div class="slip-value"><?= number_format($total_titles ?? 0) ?></div><div class="slip-sub">Titles to explore</div></div>
</div>

<div class="catalog-card">
  <div class="perf-notch"></div>
  <div class="card-inner">
    <div style="display:flex; justify-content:space-between; align-items:center"><div class="eyebrow">My loans <i></i></div><a href="/my-books" class="btn btn-ghost btn-small">View all →</a></div>
    <div class="table-wrap" style="margin-top:12px">
      <table class="data-table">
        <thead><tr><th>Book</th><th>Accession</th><th>Issued</th><th>Due</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach(($issues ?? []) as $r): ?>
          <tr>
            <td><b><?= e($r['title']) ?></b><br><span style="color:var(--slate); font-size:11px"><?= e($r['author']) ?></span></td>
            <td class="mono" style="font-size:11px"><?= e($r['accession_no'] ?? $r['book_id']) ?></td>
            <td class="mono" style="font-size:12px"><?= e($r['issue_date']) ?></td>
            <td class="mono" style="font-size:12px"><?= e($r['due_date']) ?></td>
            <td><span class="pill <?= $r['status']==='overdue'?'pill-red':($r['status']==='issued'?'pill-green':'pill-slate') ?>"><span class="pill-dot"></span><?= e($r['status']) ?></span></td>
          </tr>
        <?php endforeach; if(empty($issues)): ?><tr><td colspan="5" style="text-align:center; color:var(--slate); padding:22px">No active loans — visit the catalog.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

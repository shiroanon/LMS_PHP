<?php
$title = 'Dashboard';
?>
<div class="page-head">
  <div class="eyebrow">Ledger overview <i></i> <span class="mono" style="color:var(--slate-2)"><?= date('d M Y') ?></span></div>
  <h1>The desk <em>at a glance</em></h1>
  <p>Four slips that matter right now — then the shelves.</p>
</div>

<div class="grid-stats">
  <?php
  $cards = [
    ['label'=>'Titles in catalog','value'=>number_format($stats['titles'] ?? 2925),'sub'=>($stats['copies'] ?? 19188).' physical copies','icon'=>'▦','accent'=>'var(--paper-2)'],
    ['label'=>'Active issues','value'=>number_format($stats['active'] ?? 0),'sub'=>'Due within 14 days','icon'=>'↗','accent'=>'var(--maroon-light)'],
    ['label'=>'Overdue','value'=>number_format($stats['overdue'] ?? 0),'sub'=>'Holiday-aware net days','icon'=>'◷','accent'=>'var(--vermillion-bg)'],
    ['label'=>'Pending fines','value'=>'₹'.number_format($stats['pending_fines'] ?? 0),'sub'=>($stats['students'] ?? 0).' borrowers on file','icon'=>'₹','accent'=>'var(--brass-muted)'],
  ];
  foreach($cards as $c): ?>
  <div class="slip">
    <div class="slip-top"><span class="slip-label"><?= e($c['label']) ?></span><span class="slip-icon" style="background:<?= $c['accent'] ?>"><?= e($c['icon']) ?></span></div>
    <div class="slip-value"><?= e($c['value']) ?></div>
    <div class="slip-sub"><?= e($c['sub']) ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Charts — Ink + Brass palette, Chart.js CDN (no build) -->
<div style="display:grid; grid-template-columns:1.4fr .9fr; gap:16px; margin-top:16px">
  <div class="catalog-card">
    <div class="card-inner">
      <div class="eyebrow">Borrowers by branch <i></i> <span class="mono" style="color:var(--slate-2); font-size:11px"><?= (int)($stats['students'] ?? 0) ?> active</span></div>
      <div style="position:relative; height:260px; margin-top:10px"><canvas id="branchChart"></canvas></div>
    </div>
  </div>
  <div class="catalog-card">
    <div class="card-inner">
      <div class="eyebrow">Collection by category <i></i></div>
      <div style="position:relative; height:260px; margin-top:10px"><canvas id="catChart"></canvas></div>
    </div>
  </div>
</div>

<div class="catalog-card" style="margin-top:16px">
  <div class="card-inner" style="display:flex; gap:16px; align-items:center; flex-wrap:wrap">
    <div class="eyebrow" style="margin:0">Circulation mix <i></i></div>
    <div style="position:relative; height:120px; flex:1; min-width:220px"><canvas id="statusChart"></canvas></div>
    <div style="display:flex; gap:8px; flex-wrap:wrap; font-size:12px">
      <span class="pill pill-green"><span class="pill-dot"></span>Issued <?= (int)($statusMap['issued'] ?? 0) ?></span>
      <span class="pill pill-red"><span class="pill-dot"></span>Overdue <?= (int)($statusMap['overdue'] ?? 0) ?></span>
      <span class="pill pill-slate"><span class="pill-dot"></span>Returned <?= (int)($statusMap['returned'] ?? 0) ?></span>
    </div>
  </div>
</div>

<div class="grid-2" style="margin-top:16px">
  <div class="catalog-card">
    <div class="perf-notch"></div>
    <div class="card-inner">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px">
        <div class="eyebrow">Catalog <i></i> recent accessions</div>
        <a href="/books" class="btn btn-ghost btn-small">Open catalog →</a>
      </div>
      <div style="display:grid; gap:10px">
        <?php foreach(($recent_books ?? []) as $b): ?>
        <div style="display:flex; gap:12px; padding:12px; border:1px solid var(--border); border-radius:14px; background:white">
          <div style="width:44px; height:58px; background:var(--paper-2); border:1px solid var(--border); border-radius:8px; display:grid; place-items:center; font-size:18px">📖</div>
          <div style="flex:1; min-width:0">
            <div style="font-weight:700; font-size:13px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis"><?= e($b['title']) ?></div>
            <div style="font-size:12px; color:var(--slate)"><?= e($b['author']) ?> · <span class="mono" style="font-size:11px"><?= e($b['book_id']) ?></span></div>
            <div style="margin-top:6px"><span class="pill <?= ($b['quantity_available']>0?'pill-green':'pill-red') ?>"><span class="pill-dot"></span><?= $b['quantity_available']>0 ? e($b['quantity_available']).' available' : 'out on loan' ?></span></div>
          </div>
          <div class="stamp" style="align-self:center; font-size:10px">ACC <?= e($b['book_id']) ?></div>
        </div>
        <?php endforeach; if(empty($recent_books)): ?>
        <div class="empty" style="padding:28px"><div class="stamp">Empty shelf</div><h3>No books yet</h3><p>Seed the catalog from <span class="mono">data/branch_wise</span>.</p></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div style="display:grid; gap:16px">
    <div class="catalog-card">
      <div class="card-inner">
        <div class="eyebrow">Gate today <i></i></div>
        <div style="display:flex; align-items:baseline; gap:10px; margin-top:8px">
          <div class="display" style="font-size:34px; font-weight:900"><?= e($stats['visits_today'] ?? 0) ?></div><span style="color:var(--slate); font-size:12px">check-ins</span>
        </div>
        <div style="margin-top:12px; display:grid; gap:8px">
          <?php foreach(($visits_today ?? []) as $v): ?>
          <div style="display:flex; justify-content:space-between; font-size:12px; padding:8px 10px; background:var(--paper-2); border:1px solid var(--border); border-radius:10px">
            <span><?= e($v['name']) ?> <span class="mono" style="color:var(--slate)"><?= e($v['enrollment_no']) ?></span></span><span class="mono" style="font-size:11px"><?= e(substr($v['check_in_time'],11,5)) ?></span>
          </div>
          <?php endforeach; if(empty($visits_today)): ?><div style="font-size:12px; color:var(--slate)">No gate activity yet today.</div><?php endif; ?>
        </div>
        <a href="/visits" class="btn btn-ghost" style="width:100%; justify-content:center; margin-top:12px">Open gate register →</a>
      </div>
    </div>

    <div class="catalog-card" style="border-style:dashed">
      <div class="card-inner">
        <div class="eyebrow">Due slip <i></i> <span class="pill pill-amber" style="margin-left:auto"><span class="pill-dot"></span>Live</span></div>
        <p style="font-size:13px; color:var(--slate)">Overdue sweep runs <b style="color:var(--ink)">08:00</b> daily. Holiday days are excluded from fines (₹<?= (int)($fine_per_day ?? 2) ?>/day net).</p>
        <div style="display:flex; gap:8px; margin-top:12px">
          <a href="/fines" class="btn btn-primary btn-small">Review fines</a>
          <a href="/holidays" class="btn btn-ghost btn-small">Holiday calendar</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
const cs = getComputedStyle(document.documentElement);
const maroon = cs.getPropertyValue('--maroon').trim() || '#8B2D3B';
const brass = cs.getPropertyValue('--brass').trim() || '#C1A45A';
const ink = cs.getPropertyValue('--ink').trim() || '#0F1D2E';
const slate = cs.getPropertyValue('--slate').trim() || '#677387';
const vermillion = cs.getPropertyValue('--vermillion').trim() || '#E84855';
const success = cs.getPropertyValue('--success').trim() || '#10805A';
Chart.defaults.font.family = 'IBM Plex Sans, system-ui, sans-serif';
Chart.defaults.color = slate;

// Branch bar — horizontal, brass fills, ink border
new Chart(document.getElementById('branchChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($branchLabels ?? []) ?>,
    datasets: [{ label: 'Students', data: <?= json_encode($branchCounts ?? []) ?>, backgroundColor: [maroon, brass, ink, '#A14B59', '#E8D9B0', '#243044'], borderRadius: 8, borderSkipped: false }]
  },
  options: { indexAxis: 'y', responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false}, tooltip:{ callbacks:{ label:(c)=>' '+c.raw+' borrowers' } } }, scales:{ x:{ grid:{ color:'rgba(0,0,0,.06)' }, ticks:{ precision:0 } }, y:{ grid:{ display:false }, ticks:{ font:{ size:11 } } } } }
});

// Category doughnut
new Chart(document.getElementById('catChart'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode($catLabels ?? []) ?>,
    datasets: [{ data: <?= json_encode($catCounts ?? []) ?>, backgroundColor: [maroon, brass, ink, '#D9CCB5', vermillion, success], borderWidth: 2, borderColor: '#FDF8F0', hoverOffset: 6 }]
  },
  options: { responsive:true, maintainAspectRatio:false, cutout:'62%', plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, padding:14, font:{ size:11 } } } } }
});

// Status bar (compact horizontal)
new Chart(document.getElementById('statusChart'), {
  type: 'bar',
  data: {
    labels: ['Issued','Overdue','Returned'],
    datasets: [{ data: [<?= (int)($statusMap['issued']??0) ?>, <?= (int)($statusMap['overdue']??0) ?>, <?= (int)($statusMap['returned']??0) ?>], backgroundColor:[success, vermillion, slate], borderRadius:6 }]
  },
  options: { indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false} }, scales:{ x:{ display:false, max: Math.max(4, <?= max(4, max($statusMap) ?? 4) ?>) }, y:{ grid:{ display:false }, ticks:{ font:{ weight:600 } } } } }
});
</script>

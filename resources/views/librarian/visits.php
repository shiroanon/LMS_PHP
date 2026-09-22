<?php $title='Gate Register'; ?>
<div class="page-head">
  <div class="eyebrow">Gate <i></i> <span class="mono"><?= date('d M Y') ?> · <?= (int)($today_count ?? 0) ?> check-ins</span></div>
  <h1>Gate <em>register</em></h1>
  <p>Thumb · barcode · manual — every entry is a due-slip notch.</p>
</div>

<div class="grid-2">
  <div class="catalog-card">
    <div class="perf-notch"></div>
    <div class="card-inner">
      <div class="eyebrow">Check in / out <i></i></div>
      <form method="post" action="/visits/check" style="display:grid; gap:12px; margin-top:12px">
        <div class="field"><label>Borrower — scan or enrollment</label><input class="input mono" data-barcode-input name="member_code" required placeholder="0901CS… or library ID — scan here" autofocus></div>
        <div class="form-row"><div class="field"><label>Method</label><select name="method" class="select"><option value="barcode">Barcode</option><option value="thumb">Thumb</option><option value="manual">Manual</option></select></div><div class="field"><label>Action</label><select name="action" class="select"><option value="in">Check in</option><option value="out">Check out</option></select></div></div>
        <?php if(!empty($error)): ?><div style="background:var(--vermillion-bg); border:1px solid var(--maroon-border); color:var(--maroon); padding:10px; border-radius:12px; font-size:13px"><?= e($error) ?></div><?php endif; ?>
        <?php if(!empty($success)): ?><div style="background:var(--success-bg); border:1px solid #CFF0E3; color:var(--success); padding:10px; border-radius:12px; font-size:13px"><?= e($success) ?></div><?php endif; ?>
        <button class="btn btn-primary" style="justify-content:center">Stamp gate</button>
      </form>
    </div>
  </div>

  <div class="catalog-card" style="background:var(--paper-2)">
    <div class="card-inner">
      <div class="eyebrow">Peak hours <i></i> today</div>
      <div style="display:flex; gap:6px; align-items:end; height:90px; margin-top:14px; padding:10px; background:white; border:1px solid var(--border); border-radius:14px">
        <?php $peak = $peak_hours ?? array_fill(0,8, rand(2,10)); $max = max($peak) ?: 1; foreach($peak as $h): $hgt = (int)(($h/$max)*70)+8; ?>
        <div style="flex:1; background:linear-gradient(180deg, var(--maroon), var(--brass)); border-radius:6px; height:<?= $hgt ?>px"></div>
        <?php endforeach; ?>
      </div>
      <div style="display:flex; justify-content:space-between; font-size:10px; color:var(--slate); margin-top:6px" class="mono"><span>9a</span><span>12p</span><span>4p</span></div>
    </div>
  </div>
</div>

<div class="table-wrap" style="margin-top:16px">
  <table class="data-table">
    <thead><tr><th>Time</th><th>Borrower</th><th>Enrollment</th><th>Method</th><th>Stay</th></tr></thead>
    <tbody>
    <?php foreach(($visits ?? []) as $v): ?>
      <tr><td class="mono" style="font-size:12px"><?= e(substr($v['check_in_time'],11,5)) ?> → <?= e($v['check_out_time'] ? substr($v['check_out_time'],11,5) : '—') ?></td><td><b><?= e($v['name']) ?></b></td><td class="mono" style="font-size:12px"><?= e($v['enrollment_no']) ?></td><td><span class="pill pill-slate"><?= e($v['method']) ?></span></td><td class="mono" style="font-size:12px"><?= e($v['check_out_time'] ? 'closed' : 'inside') ?></td></tr>
    <?php endforeach; if(empty($visits)): ?><tr><td colspan="5" style="text-align:center; color:var(--slate); padding:22px">No visits today — gate is quiet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

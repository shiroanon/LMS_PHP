<?php $title='Staff';
$sortKey = $sortKey ?? 'name'; $sortDir = (strtolower($sortOrder ?? '') === 'desc') ? 'desc' : 'asc';
$keepQs = 'search='.urlencode($_GET['search'] ?? '').'&loan_category='.urlencode($_GET['loan_category'] ?? '');
$sortLink = function($key) use ($sortKey,$sortDir,$keepQs){
  $d = ($sortKey===$key) ? ($sortDir==='asc'?'desc':'asc') : 'asc';
  return "/staff?$keepQs&sort_by=$key&sort_order=$d";
};
$sortArrow = function($key) use ($sortKey,$sortDir){
  if($sortKey!==$key) return '<span style="color:var(--slate-2); opacity:.55">↕</span>';
  return $sortDir==='asc' ? '▲' : '▼';
};
$thStyle = 'cursor:pointer; white-space:nowrap; user-select:none';
$thLink = 'color:inherit; text-decoration:none';
?>
<div class="page-head" style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:end">
  <div>
    <div class="eyebrow">People <i></i> <span class="mono"><?= (int)$total ?> staff</span></div>
    <h1>Staff <em>members</em></h1>
    <p>Department, library & teaching staff — click a row for loans & fines.</p>
  </div>
  <a href="/staff/new" class="btn btn-primary">+ Add staff</a>
</div>

<div class="catalog-card" style="margin-bottom:14px">
  <div class="card-inner" style="display:flex; gap:10px; flex-wrap:wrap">
    <form method="get" action="/staff" class="search" style="flex:1; min-width:220px">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" name="search" value="<?= e($_GET['search'] ?? '') ?>" placeholder="Name / library ID / department">
    </form>
    <form method="get" action="/staff" style="display:flex; gap:8px">
      <input type="hidden" name="search" value="<?= e($_GET['search'] ?? '') ?>">
      <input type="hidden" name="sort_by" value="<?= e($sortKey) ?>">
      <input type="hidden" name="sort_order" value="<?= e($sortDir) ?>">
      <select name="loan_category" class="select" onchange="this.form.submit()">
        <option value="">All departments</option>
        <?php foreach($cats as $c): ?><option value="<?= e($c) ?>" <?= (($_GET['loan_category'] ?? '')===$c?'selected':'') ?>><?= e($c) ?></option><?php endforeach; ?>
      </select>
      <button class="btn btn-ghost btn-small">Filter</button>
    </form>
  </div>
</div>

<div class="table-wrap">
  <table class="data-table">
    <thead><tr><th style="<?= $thStyle ?>" title="Sort by staff ID"><a href="<?= $sortLink('staff_id') ?>" style="<?= $thLink ?>">Staff ID <?= $sortArrow('staff_id') ?></a></th><th style="<?= $thStyle ?>" title="Sort by name"><a href="<?= $sortLink('name') ?>" style="<?= $thLink ?>">Name <?= $sortArrow('name') ?></a></th><th style="<?= $thStyle ?>" title="Sort by department"><a href="<?= $sortLink('department') ?>" style="<?= $thLink ?>">Department <?= $sortArrow('department') ?></a></th><th style="<?= $thStyle ?>" title="Sort by type"><a href="<?= $sortLink('type') ?>" style="<?= $thLink ?>">Type <?= $sortArrow('type') ?></a></th><th style="<?= $thStyle ?>" title="Sort by status"><a href="<?= $sortLink('status') ?>" style="<?= $thLink ?>">Status <?= $sortArrow('status') ?></a></th><th></th></tr></thead>
    <tbody>
    <?php foreach($staff as $s): ?>
      <tr onclick="MemberCard.open('staff', <?= (int)$s['id'] ?>)" style="cursor:pointer" title="Open staff card">
        <td class="mono" style="font-weight:700; color:var(--maroon)"><?= e($s['library_id']) ?></td>
        <td><b><?= e($s['name']) ?></b></td>
        <td><span class="pill pill-slate"><?= e($s['loan_category'] ?: '—') ?></span></td>
        <td style="font-size:12px; color:var(--slate)"><?= e($s['membership_type'] ?: '—') ?></td>
        <td><span class="pill <?= ($s['status'] ?? 'active')==='active'?'pill-green':'pill-red' ?>"><span class="pill-dot"></span><?= e($s['status'] ?? 'active') ?></span></td>
        <td onclick="event.stopPropagation()"><a href="/staff/<?= (int)$s['id'] ?>" class="btn btn-ghost btn-small">Open →</a></td>
      </tr>
    <?php endforeach; if(empty($staff)): ?><tr><td colspan="6" style="text-align:center; color:var(--slate); padding:24px">No staff match.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php if($pages>1):
  $current=(int)($page??1); $delta=2; $range=[];
  for($i=1;$i<=$pages;$i++){ if($i==1||$i==$pages||($i>=$current-$delta&&$i<=$current+$delta)) $range[]=$i; elseif(end($range)!=='...') $range[]='...'; }
  $qs=urlencode($_GET['search']??''); $qc=urlencode($_GET['loan_category']??''); $base="/staff?search=$qs&loan_category=$qc&sort_by=$sortKey&sort_order=$sortDir&page=";
?>
<div class="pagination" style="display:flex; gap:6px; justify-content:center; margin-top:18px; flex-wrap:wrap">
  <a href="<?= $current>1?$base.($current-1):'#' ?>" class="btn btn-ghost btn-small" style="<?= $current<=1?'opacity:.35;pointer-events:none':'' ?>">‹</a>
  <?php foreach($range as $p): if($p==='...'): ?><span style="width:36px; display:flex; align-items:center; justify-content:center; color:var(--slate)">…</span><?php else: ?><a href="<?= $base.$p ?>" class="btn <?= $p==$current?'btn-primary':'btn-ghost' ?> btn-small" style="min-width:36px; justify-content:center"><?= $p ?></a><?php endif; endforeach; ?>
  <a href="<?= $current<$pages?$base.($current+1):'#' ?>" class="btn btn-ghost btn-small" style="<?= $current>=$pages?'opacity:.35;pointer-events:none':'' ?>">›</a>
</div>
<?php endif; ?>

<?php include dirname(__DIR__).'/partials/member_card.php'; ?>

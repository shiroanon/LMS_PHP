<?php $title='Students';
$sortKey = $sortKey ?? 'created'; $sortDir = (strtolower($sortOrder ?? '') === 'asc') ? 'asc' : 'desc';
$keepQs = 'q='.urlencode($_GET['q'] ?? '').'&branch='.urlencode($_GET['branch'] ?? '').'&year='.urlencode($_GET['year'] ?? '');
$sortLink = function($key) use ($sortKey,$sortDir,$keepQs){
  $d = ($sortKey===$key) ? ($sortDir==='asc'?'desc':'asc') : 'asc';
  return "/students?$keepQs&sort_by=$key&sort_order=$d";
};
$sortArrow = function($key) use ($sortKey,$sortDir){
  if($sortKey!==$key) return '<span style="color:var(--slate-2); opacity:.55">↕</span>';
  return $sortDir==='asc' ? '▲' : '▼';
};
$thStyle = 'cursor:pointer; white-space:nowrap; user-select:none';
$thLink = 'color:inherit; text-decoration:none';
?>

<div class="catalog-card" style="margin-bottom:16px">
  <div class="card-inner" style="display:flex; gap:10px; flex-wrap:wrap">
    <form method="get" action="/students" class="search" style="flex:1; min-width:220px">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Name / enrollment / library ID">
    </form>
    <form method="get" action="/students" style="display:flex; gap:8px; flex-wrap:wrap">
      <input type="hidden" name="q" value="<?= e($_GET['q'] ?? '') ?>">
      <input type="hidden" name="sort_by" value="<?= e($sortKey) ?>">
      <input type="hidden" name="sort_order" value="<?= e($sortDir) ?>">
      <select name="branch" class="select" onchange="this.form.submit()"><option value="">All branches</option><?php foreach(($branches ?? []) as $b): ?><option <?= (($_GET['branch'] ?? '')===$b?'selected':'') ?>><?= e($b) ?></option><?php endforeach; ?></select>
      <select name="year" class="select" onchange="this.form.submit()"><option value="">All years</option><?php for($y=1;$y<=4;$y++): ?><option value="<?= $y ?>" <?= (($_GET['year'] ?? '')== (string)$y?'selected':'') ?>><?= $y ?> yr</option><?php endfor; ?></select>
      <button class="btn btn-ghost btn-small">Filter</button>
    </form>
    <div style="display:flex; gap:8px; align-items:center; margin-left:auto">
      <a href="/students/import" class="btn btn-ghost btn-small">Import CSV</a>
      <a href="/students/new" class="btn btn-primary btn-small">+ New student</a>
    </div>
  </div>
</div>

<div class="table-wrap">
  <table class="data-table">
    <thead><tr><th style="<?= $thStyle ?>" title="Sort by name"><a href="<?= $sortLink('name') ?>" style="<?= $thLink ?>">Borrower <?= $sortArrow('name') ?></a></th><th style="<?= $thStyle ?>" title="Sort by enrollment"><a href="<?= $sortLink('enrollment') ?>" style="<?= $thLink ?>">Enrollment <?= $sortArrow('enrollment') ?></a></th><th style="<?= $thStyle ?>" title="Sort by branch"><a href="<?= $sortLink('branch') ?>" style="<?= $thLink ?>">Branch <?= $sortArrow('branch') ?></a></th><th style="<?= $thStyle ?>" title="Sort by year"><a href="<?= $sortLink('year') ?>" style="<?= $thLink ?>">Year <?= $sortArrow('year') ?></a></th><th style="<?= $thStyle ?>" title="Sort by status"><a href="<?= $sortLink('status') ?>" style="<?= $thLink ?>">Status <?= $sortArrow('status') ?></a></th><th style="<?= $thStyle ?>" title="Sort by active loans"><a href="<?= $sortLink('active') ?>" style="<?= $thLink ?>">Active <?= $sortArrow('active') ?></a></th><th></th></tr></thead>
    <tbody>
    <?php foreach(($students ?? []) as $s): ?>
      <tr onclick="MemberCard.open('students', <?= (int)$s['id'] ?>)" style="cursor:pointer" title="Open borrower card">
        <td><?php $noLib = empty($s['library_id']); ?><b<?= $noLib ? ' style="color:var(--vermillion)"' : '' ?>><?= e($s['name']) ?></b><br><?php if($noLib): ?><span class="pill pill-red" style="font-size:10px"><span class="pill-dot"></span>No library ID</span><?php else: ?><span style="color:var(--slate); font-size:11px" class="mono"><?= e($s['library_id']) ?></span><?php endif; ?></td>
        <td class="mono" style="font-size:12px"><?= e($s['enrollment_no']) ?></td>
        <td><span class="pill pill-slate"><?= e($s['branch']) ?></span></td>
        <td class="mono"><?= (int)$s['year'] ?></td>
        <td><span class="pill <?= $s['status']==='active'?'pill-green':($s['status']==='passed_out'?'pill-amber':'pill-red') ?>"><span class="pill-dot"></span><?= e($s['status']) ?></span></td>
        <td class="mono" style="text-align:center"><?= (int)($s['active_count'] ?? 0) ?></td>
        <td onclick="event.stopPropagation()" style="white-space:nowrap"><a href="/students/<?= (int)$s['id'] ?>?edit=1" class="btn btn-ghost btn-small">Edit</a> <a href="/students/<?= (int)$s['id'] ?>" class="btn btn-ghost btn-small">Open →</a></td>
      </tr>
    <?php endforeach; if(empty($students)): ?><tr><td colspan="7" style="text-align:center; color:var(--slate); padding:24px">No borrowers match.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php if(!empty($pages) && $pages>1):
  $current=(int)($page ?? 1); $delta=2; $range=[];
  for($i=1;$i<=$pages;$i++){ if($i==1||$i==$pages||($i>=$current-$delta&&$i<=$current+$delta)) $range[]=$i; elseif(end($range)!=='...') $range[]='...'; }
  $qs=urlencode($_GET['q'] ?? ''); $qb=urlencode($_GET['branch'] ?? ''); $qy=urlencode($_GET['year'] ?? '');
  $base="/students?q=$qs&branch=$qb&year=$qy&sort_by=$sortKey&sort_order=$sortDir&page=";
?>
<div class="pagination" style="display:flex; gap:6px; justify-content:center; margin-top:18px; flex-wrap:wrap; align-items:center">
  <a href="<?= $current>1?$base.($current-1):'#' ?>" class="btn btn-ghost btn-small" style="<?= $current<=1?'opacity:.35;pointer-events:none':'' ?>">‹</a>
  <?php foreach($range as $p): if($p==='...'): ?><span style="width:36px; display:flex; align-items:center; justify-content:center; color:var(--slate)">…</span><?php else: ?><a href="<?= $base.$p ?>" class="btn <?= $p==$current?'btn-primary':'btn-ghost' ?> btn-small" style="min-width:36px; justify-content:center"><?= $p ?></a><?php endif; endforeach; ?>
  <a href="<?= $current<$pages?$base.($current+1):'#' ?>" class="btn btn-ghost btn-small" style="<?= $current>=$pages?'opacity:.35;pointer-events:none':'' ?>">›</a>
</div>
<div style="text-align:center; margin-top:8px; font-size:11px; color:var(--slate)" class="mono">Page <?= $current ?> of <?= $pages ?> · <?= (int)($totalFiltered ?? $total) ?> match · <?= (int)$total ?> total</div>
<?php else: ?>
<div style="text-align:center; margin-top:10px; font-size:11px; color:var(--slate)" class="mono"><?= (int)($totalFiltered ?? $total) ?> borrowers</div>
<?php endif; ?>

<?php include dirname(__DIR__).'/partials/member_card.php'; ?>

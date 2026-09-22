<?php $title='Catalog';
$viewMode = $viewMode ?? 'cards';
$sortKey = $sortKey ?? 'title'; $sortDir = (strtolower($sortOrder ?? '') === 'desc') ? 'desc' : 'asc';
$keepQs = 'search='.urlencode($_GET['search'] ?? '').'&category='.urlencode($_GET['category'] ?? '').(!empty($_GET['available'])?'&available=1':'');
$sortLink = function($key) use ($sortKey,$sortDir,$keepQs,$viewMode){
  $d = ($sortKey===$key) ? ($sortDir==='asc'?'desc':'asc') : 'asc';
  return "/books?$keepQs&view=$viewMode&sort_by=$key&sort_order=$d";
};
$sortArrow = function($key) use ($sortKey,$sortDir){
  if($sortKey!==$key) return '<span style="color:var(--slate-2); opacity:.55">↕</span>';
  return $sortDir==='asc' ? '▲' : '▼';
};
$modeLink = function($v) use ($keepQs,$sortKey,$sortDir){
  return "/books?$keepQs&view=$v&sort_by=$sortKey&sort_order=$sortDir";
};
$thStyle = 'cursor:pointer; white-space:nowrap; user-select:none';
$thLink = 'color:inherit; text-decoration:none';
?>
<div class="page-head" style="display:flex; gap:16px; align-items:end; justify-content:space-between; flex-wrap:wrap">
  <div>
    <div class="eyebrow">Catalog <i></i> <span class="mono"><?= (int)($total ?? 0) ?> titles · <?= (int)($copies ?? 0) ?> copies</span></div>
  </div>
  <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap">
    <div style="display:flex; border:1px solid var(--border); border-radius:12px; overflow:hidden">
      <a href="<?= $modeLink('cards') ?>" class="btn btn-small" style="<?= $viewMode!=='table'?'background:var(--maroon); color:#fff':'background:transparent; color:var(--ink-soft)' ?>; border:none; border-radius:0">Cards</a>
      <a href="<?= $modeLink('table') ?>" class="btn btn-small" style="<?= $viewMode==='table'?'background:var(--maroon); color:#fff':'background:transparent; color:var(--ink-soft)' ?>; border:none; border-radius:0">Table</a>
    </div>
    <a href="/books/new" class="btn btn-primary">+ New accession</a>
  </div>
</div>

<div class="catalog-card" style="margin-bottom:16px">
  <div class="card-inner" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center">
    <form method="get" action="/books" class="search" style="flex:1; min-width:240px">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" name="search" value="<?= e($_GET['search'] ?? '') ?>" placeholder="Search title / author / accession / ISBN">
      <input type="hidden" name="category" value="<?= e($_GET['category'] ?? '') ?>">
      <?php if(!empty($_GET['available'])): ?><input type="hidden" name="available" value="1"><?php endif; ?>
      <input type="hidden" name="view" value="<?= e($viewMode) ?>">
      <input type="hidden" name="sort_by" value="<?= e($sortKey) ?>">
      <input type="hidden" name="sort_order" value="<?= e($sortDir) ?>">
    </form>
    <form method="get" action="/books" style="display:flex; gap:8px">
      <input type="hidden" name="search" value="<?= e($_GET['search'] ?? '') ?>">
      <input type="hidden" name="view" value="<?= e($viewMode) ?>">
      <input type="hidden" name="sort_by" value="<?= e($sortKey) ?>">
      <input type="hidden" name="sort_order" value="<?= e($sortDir) ?>">
      <select name="category" class="select" style="padding:10px 12px; border-radius:12px" onchange="this.form.submit()">
        <option value="">All departments</option>
        <?php foreach(($categories ?? []) as $c): ?><option value="<?= e($c) ?>" <?= (($_GET['category'] ?? '')===$c?'selected':'') ?>><?= e($c) ?></option><?php endforeach; ?>
      </select>
      <label style="display:flex; gap:6px; align-items:center; font-size:13px; color:var(--slate)"><input type="checkbox" name="available" value="1" <?= !empty($_GET['available'])?'checked':'' ?> onchange="this.form.submit()"> Available</label>
      <button class="btn btn-ghost btn-small">Filter</button>
    </form>
  </div>
</div>

<?php if($viewMode==='table'): ?>
<div class="table-wrap">
  <table class="data-table">
    <thead><tr><th></th><th style="<?= $thStyle ?>" title="Sort by title"><a href="<?= $sortLink('title') ?>" style="<?= $thLink ?>">Title <?= $sortArrow('title') ?></a></th><th style="<?= $thStyle ?>" title="Sort by author"><a href="<?= $sortLink('author') ?>" style="<?= $thLink ?>">Author <?= $sortArrow('author') ?></a></th><th style="<?= $thStyle ?>" title="Sort by accession"><a href="<?= $sortLink('accession') ?>" style="<?= $thLink ?>">Accession <?= $sortArrow('accession') ?></a></th><th style="<?= $thStyle ?>" title="Sort by department"><a href="<?= $sortLink('category') ?>" style="<?= $thLink ?>">Department <?= $sortArrow('category') ?></a></th><th style="<?= $thStyle ?>" title="Sort by shelf"><a href="<?= $sortLink('shelf') ?>" style="<?= $thLink ?>">Shelf <?= $sortArrow('shelf') ?></a></th><th style="<?= $thStyle ?>" title="Sort by availability"><a href="<?= $sortLink('avail') ?>" style="<?= $thLink ?>">Avail <?= $sortArrow('avail') ?></a></th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach(($books ?? []) as $b): ?>
      <tr onclick="location.href='/books/<?= (int)$b['id'] ?>'" style="cursor:pointer" title="Open <?= e($b['title']) ?>">
        <td><?php if(!empty($b['book_image_url'])): ?><img src="<?= e($b['book_image_url']) ?>" alt="" loading="lazy" style="width:36px; height:48px; object-fit:cover; border:1px solid var(--border); border-radius:6px; background:#fff; display:block"><?php else: ?><span class="mono" style="color:var(--slate-2)">—</span><?php endif; ?></td>
        <td><b><?= e($b['title']) ?></b></td>
        <td><?= e($b['author']) ?></td>
        <td class="mono" style="font-weight:700"><?= e($b['book_id']) ?></td>
        <td><span class="pill pill-slate" style="font-size:10px"><?= e($b['category'] ?? 'General') ?></span></td>
        <td class="mono"><?= e($b['shelf_location'] ?? '—') ?></td>
        <td class="mono"><b><?= (int)$b['quantity_total'] ?></b> · <b style="color:<?= $b['quantity_available']>0 ? 'var(--success)' : 'var(--vermillion)' ?>"><?= (int)$b['quantity_available'] ?></b></td>
        <td><span class="pill <?= $b['quantity_available']>0?'pill-green':'pill-red' ?>"><span class="pill-dot"></span><?= $b['quantity_available']>0?'on shelf':'out' ?></span></td>
        <td onclick="event.stopPropagation()" style="white-space:nowrap"><button type="button" data-book-id="<?= (int)$b['id'] ?>" data-book-title="<?= e($b['title']) ?>" onclick="openIssuePicker(this.dataset.bookId, this.dataset.bookTitle)" class="btn btn-primary btn-small">Issue</button></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:14px">
  <?php foreach(($books ?? []) as $b): ?>
  <div class="book-card">
    <div class="book-perf"></div>
    <?php if(!empty($b['book_image_url'])): ?>
    <a href="/books/<?= (int)$b['id'] ?>" style="display:block; padding:12px 12px 0; background:var(--paper-3)" aria-label="View <?= e($b['title']) ?>">
      <img src="<?= e($b['book_image_url']) ?>" alt="Cover of <?= e($b['title']) ?>" loading="lazy" onerror="this.closest('a').remove()" style="display:block; width:100%; height:180px; object-fit:cover; border:1px solid var(--border); border-radius:12px; background:white">
    </a>
    <?php endif; ?>
    <div class="book-body">
      <div style="display:flex; justify-content:space-between; gap:8px; align-items:start; flex-wrap:wrap">
        <div style="display:flex; gap:4px; flex-wrap:wrap">
        <?php $cats = $b['categories'] ?? ($b['category'] ? [$b['category']] : []); foreach($cats as $cc): ?><span class="pill pill-slate" style="font-size:10px"><span class="pill-dot"></span><?= e($cc) ?></span><?php endforeach; if(empty($cats)): ?><span class="pill pill-slate" style="font-size:10px"><span class="pill-dot"></span>General</span><?php endif; ?>
        <?php if(!empty($b['has_reference'])): ?><span class="pill pill-amber" style="font-size:10px"><span class="pill-dot"></span>Reference</span><?php endif; ?>
        </div>
        <span class="stamp" style="font-size:10px">ACC <?= e($b['book_id']) ?></span>
      </div>
      <div class="book-title" style="margin-top:10px"><?= e($b['title']) ?></div>
      <div class="book-author"><?= e($b['author']) ?></div>

      <?php $hasCover = !empty($b['book_image_url']); if($hasCover): ?><details class="bib-collapse"><summary><span>Details</span><span class="mono"><?= e($b['shelf_location'] ?? '') ?></span></summary><?php endif; ?>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px 12px; margin-top:10px; font-size:11px; line-height:1.4; background:var(--paper-2); border:1px solid var(--border); border-radius:10px; padding:8px 10px">
        <div><span style="color:var(--slate); font-weight:700; letter-spacing:.06em; font-size:10px">ISBN</span><div class="mono" style="font-weight:600"><?= e($b['isbn'] ?? '—') ?></div></div>
        <div><span style="color:var(--slate); font-weight:700; letter-spacing:.06em; font-size:10px">ISSN</span><div class="mono" style="font-weight:600"><?= e($b['issn'] ?? '—') ?></div></div>
        <div><span style="color:var(--slate); font-weight:700; letter-spacing:.06em; font-size:10px">EDITION</span><div><?= e($b['edition'] ?? '—') ?></div></div>
        <div><span style="color:var(--slate); font-weight:700; letter-spacing:.06em; font-size:10px">PUBLICATION</span><div><?= e($b['publication'] ?? '—') ?></div></div>
        <div><span style="color:var(--slate); font-weight:700; letter-spacing:.06em; font-size:10px">PAGES</span><div class="mono"><?= e($b['num_pages'] ?? '—') ?></div></div>
        <div><span style="color:var(--slate); font-weight:700; letter-spacing:.06em; font-size:10px">DEPARTMENT</span><div><?= e($b['category'] ?? 'General') ?></div></div>
        <div><span style="color:var(--slate); font-weight:700; letter-spacing:.06em; font-size:10px">SHELF</span><div class="mono"><?= e($b['shelf_location'] ?? '—') ?></div></div>
        <div><span style="color:var(--slate); font-weight:700; letter-spacing:.06em; font-size:10px">COPIES</span><div class="mono"><b><?= (int)$b['quantity_total'] ?></b> total · <b style="color:<?= $b['quantity_available']>0 ? 'var(--success)' : 'var(--vermillion)' ?>"><?= (int)$b['quantity_available'] ?></b> avail</div></div>
        <?php if(!empty($b['supplier_name'])): ?><div style="grid-column:1 / -1"><span style="color:var(--slate); font-weight:700; letter-spacing:.06em; font-size:10px">SUPPLIER</span><div><?= e($b['supplier_name']) ?><?php if(!empty($b['purchase_price'])): ?> · <span class="mono">₹<?= e($b['purchase_price']) ?></span><?php endif; ?></div></div><?php endif; ?>
      </div>
      <?php if($hasCover): ?></details><?php endif; ?>

      <div class="book-foot" style="margin-top:10px">
        <span class="avail"><b><?= (int)$b['quantity_available'] ?></b> <i>/ <?= (int)$b['quantity_total'] ?> available</i></span>
        <span class="pill <?= $b['quantity_available']>0?'pill-green':'pill-red' ?>"><span class="pill-dot"></span><?= $b['quantity_available']>0?'on shelf':'out' ?></span>
      </div>
      <div style="display:flex; gap:8px; margin-top:12px">
        <a href="/books/<?= (int)$b['id'] ?>" class="btn btn-ghost btn-small" style="flex:1; justify-content:center">View card</a>
        <button type="button" data-book-id="<?= (int)$b['id'] ?>" data-book-title="<?= e($b['title']) ?>" onclick="openIssuePicker(this.dataset.bookId, this.dataset.bookTitle)" class="btn btn-primary btn-small" style="flex:1; justify-content:center">Issue</button>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if(empty($books)): ?>
<div class="catalog-card" style="margin-top:16px"><div class="card-inner empty"><div class="stamp">No matches</div><h3>Nothing on this shelf</h3><p>Try a broader search or check another department.</p></div></div>
<?php endif; ?>

<?php if(!empty($pages) && $pages>1):
  $current = (int)($page ?? $_GET['page'] ?? 1);
  $delta = 2;
  $range = [];
  for($i=1;$i<=$pages;$i++){
    if($i==1 || $i==$pages || ($i >= $current - $delta && $i <= $current + $delta)){
      $range[] = $i;
    } elseif(end($range) !== '...'){
      $range[] = '...';
    }
  }
  $qS = urlencode($_GET['search'] ?? '');
  $qC = urlencode($_GET['category'] ?? '');
  $qA = !empty($_GET['available']) ? '&available=1' : '';
  $base = "/books?search=$qS&category=$qC{$qA}&view=$viewMode&sort_by=$sortKey&sort_order=$sortDir&page=";
?>
<div class="pagination" style="display:flex; gap:6px; justify-content:center; margin-top:20px; flex-wrap:wrap; align-items:center">
  <a href="<?= $current>1 ? $base.($current-1) : '#' ?>" class="btn btn-ghost btn-small" style="<?= $current<=1?'opacity:.35;pointer-events:none':'' ?>">‹</a>
  <?php foreach($range as $p): ?>
    <?php if($p==='...'): ?><span class="pagination-ellipsis" style="width:36px; height:36px; display:flex; align-items:center; justify-content:center; color:var(--slate)">…</span>
    <?php else: ?><a href="<?= $base.$p ?>" class="btn <?= $p==$current?'btn-primary':'btn-ghost' ?> btn-small" style="min-width:36px; justify-content:center"><?= $p ?></a>
    <?php endif; ?>
  <?php endforeach; ?>
  <a href="<?= $current<$pages ? $base.($current+1) : '#' ?>" class="btn btn-ghost btn-small" style="<?= $current>=$pages?'opacity:.35;pointer-events:none':'' ?>">›</a>
</div>
<div style="text-align:center; margin-top:8px; font-size:11px; color:var(--slate)" class="mono">Page <?= $current ?> of <?= $pages ?> · <?= (int)$total ?> titles</div>
<?php endif; ?>

<!-- Issue copy picker modal -->
<div id="issuePicker" class="modal-overlay" style="display:none">
  <div class="modal" onclick="event.stopPropagation()" style="max-width:480px">
    <div class="modal-header"><h2>Select copy to issue</h2><button class="btn btn-ghost btn-icon" onclick="closeIssuePicker()" aria-label="Close"><svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg></button></div>
    <div class="modal-body">
      <div id="issuePickerTitle" style="font-weight:800; font-size:14px"></div>
      <div id="issuePickerList" style="display:grid; gap:8px; margin-top:10px"></div>
      <div id="issuePickerEmpty" style="display:none; text-align:center; padding:16px; color:var(--slate)">No available copies — all issued or reference.</div>
    </div>
    <div class="modal-footer"><button class="btn btn-ghost" onclick="closeIssuePicker()">Cancel</button></div>
  </div>
</div>

<script>
function openIssuePicker(bookId, title){
  document.getElementById('issuePickerTitle').textContent = title;
  const list=document.getElementById('issuePickerList');
  const empty=document.getElementById('issuePickerEmpty');
  list.innerHTML='<div style="text-align:center; padding:12px; color:var(--slate)">Loading copies…</div>';
  empty.style.display='none';
  document.getElementById('issuePicker').style.display='flex';
  fetch('/books/'+bookId+'/copies', {credentials:'same-origin', headers:{'Accept':'application/json'}}).then(r=>{ if(!r.ok) throw new Error('auth'); return r.json(); }).then(copies=>{
    const avail = copies.filter(c=>c.status==='available');
    if(!avail.length){
      list.innerHTML='';
      empty.style.display='';
      // show all with status for info
      copies.forEach(c=>{
        const row=document.createElement('div');
        row.style.cssText='display:flex; justify-content:space-between; padding:8px 10px; background:var(--paper-2); border:1px solid var(--border); border-radius:10px; font-size:13px; opacity:.6';
        row.innerHTML='<span class="mono" style="font-weight:700">'+c.barcode_data+'</span><span class="pill '+(c.status==='available'?'pill-green':'pill-amber')+'"><span class="pill-dot"></span>'+c.status+'</span>';
        list.appendChild(row);
      });
      return;
    }
    list.innerHTML='';
    avail.forEach(c=>{
      const row=document.createElement('div');
      row.style.cssText='display:flex; align-items:center; justify-content:space-between; gap:10px; padding:10px 12px; background:white; border:1px solid var(--border); border-radius:12px';
      row.innerHTML='<div><div class="mono" style="font-weight:800">'+c.barcode_data+'</div><div style="font-size:11px; color:var(--slate)">Barcode = '+c.barcode_data+'</div></div><a href="/issue?book='+encodeURIComponent(c.barcode_data)+'" class="btn btn-primary btn-small">Issue this copy</a>';
      list.appendChild(row);
    });
  }).catch(()=>{ list.innerHTML='<div style="color:var(--vermillion)">Failed to load copies</div>'; });
}
function closeIssuePicker(){ document.getElementById('issuePicker').style.display='none'; }
document.getElementById('issuePicker').addEventListener('click', closeIssuePicker);
</script>

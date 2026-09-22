<?php $title = $book['title'] ?? 'Book'; $isLib = ($user['role']??'')==='librarian'; $departments = $departments ?? []; ?>
<style>
.dept-grid{display:flex; flex-wrap:wrap; gap:8px}
.dept-check{display:flex; align-items:center; gap:6px; padding:6px 12px; border:1px solid var(--border); border-radius:8px; cursor:pointer; font-size:13px !important; font-weight:400 !important; letter-spacing:0 !important; text-transform:none !important; color:var(--ink-soft) !important; background:transparent; user-select:none}
.dept-check:has(input:checked){background:var(--maroon-light); border-color:var(--maroon-border)}
.dept-check input{accent-color:var(--maroon); width:15px; height:15px; margin:0}
</style>
<div class="page-head">
  <a href="/books" class="btn btn-ghost btn-small">← Back to catalog</a>
  <div class="eyebrow" style="margin-top:10px">Accession <i></i> <span class="mono"><?= e($book['book_id']) ?></span> · <span class="mono">BARCODE = <?= e($book['barcode_data'] ?? $book['book_id']) ?></span></div>
  <h1><?= e($book['title']) ?> <em>— <?= e($book['author']) ?></em></h1>
  <p>
    <?php foreach(($cats ?? []) as $c): ?><span class="pill pill-slate" style="margin-right:6px"><?= e($c) ?></span><?php endforeach; ?>
    <?php if(empty($cats)): ?><span class="pill pill-slate">General</span><?php endif; ?>
    <?php if(!empty($book['has_reference'])): ?><span class="pill pill-amber">Reference</span><?php endif; ?>
    · Shelf <?= e($book['shelf_location'] ?? '—') ?> · <span class="pill <?= $book['quantity_available']>0?'pill-green':'pill-red' ?>"><span class="pill-dot"></span><?= (int)$book['quantity_available'] ?> / <?= (int)$book['quantity_total'] ?> available</span>
  </p>
</div>

<?php if($isLib): ?>
<div style="display:flex; gap:8px; margin-bottom:14px; flex-wrap:wrap">
  <button class="btn btn-primary btn-small" onclick="document.getElementById('editPanel').style.display=document.getElementById('editPanel').style.display==='none'?'':'none'">✏️ Edit book</button>
  <button class="btn btn-ghost btn-small" onclick="openDetailIssuePicker()">Issue book →</button>
  <a href="/barcode/render?text=<?= urlencode($book['barcode_data'] ?? $book['book_id']) ?>" target="_blank" class="btn btn-ghost btn-small">Barcode</a>
</div>
<div id="editPanel" class="catalog-card" style="display:none; margin-bottom:16px">
  <div class="card-inner">
    <div class="eyebrow">Edit — all fields <i></i> accession = barcode</div>
    <form method="post" action="/books/<?= (int)$book['id'] ?>" enctype="multipart/form-data" style="display:grid; gap:12px; margin-top:12px">
      <div class="form-row"><div class="field"><label>Title *</label><input class="input" name="title" value="<?= e($book['title']) ?>" required></div><div class="field"><label>Author *</label><input class="input" name="author" value="<?= e($book['author']) ?>" required></div></div>
      <div class="field"><label>Cover page <span style="font-weight:400; letter-spacing:0; text-transform:none; color:var(--slate)">— JPG / PNG / WebP · max 5 MB</span></label>
        <?php if(!empty($book['book_image_url'])): ?>
        <div style="display:flex; gap:10px; align-items:center; margin-bottom:8px">
          <img src="<?= e($book['book_image_url']) ?>" alt="Current cover" style="height:72px; border:1px solid var(--border); border-radius:8px; background:white; padding:3px">
          <label style="display:flex; gap:6px; align-items:center; font-size:13px; color:var(--ink-soft)"><input type="checkbox" name="remove_cover" value="1" style="accent-color:var(--maroon)"> Remove cover</label>
        </div>
        <?php endif; ?>
        <input class="input" type="file" name="cover" accept="image/jpeg,image/png,image/webp">
      </div>
      <div class="form-row"><div class="field"><label>ISBN</label><input class="input mono" name="isbn" value="<?= e($book['isbn'] ?? '') ?>" placeholder="978-..."></div><div class="field"><label>ISSN</label><input class="input mono" name="issn" value="<?= e($book['issn'] ?? '') ?>" placeholder="XXXX-XXXX"></div></div>
      <div class="form-row"><div class="field"><label>Departments</label>
        <?php if(!empty($departments)): ?>
        <div class="dept-grid">
          <input type="hidden" name="categories_present" value="1">
          <?php foreach($departments as $d): ?><label class="dept-check"><input type="checkbox" name="categories[]" value="<?= e($d) ?>" <?= in_array($d, $cats ?? [], true) ? 'checked' : '' ?>><?= e($d) ?></label><?php endforeach; ?>
        </div>
        <?php else: ?>
        <input class="input" name="category" value="<?= e(implode(', ', $cats ?? [])) ?>" placeholder="CSE, EC">
        <?php endif; ?>
      </div><div class="field"><label>Edition</label><input class="input" name="edition" value="<?= e($book['edition'] ?? '') ?>" placeholder="3rd Edition"></div></div>
      <div class="form-row"><div class="field"><label>Publication / Publisher</label><input class="input" name="publication" value="<?= e($book['publication'] ?? '') ?>"></div><div class="field"><label>Pages</label><input class="input" type="number" name="num_pages" value="<?= e($book['num_pages'] ?? '') ?>"></div></div>
      <div class="form-row"><div class="field"><label>Shelf location</label><input class="input mono" name="shelf_location" value="<?= e($book['shelf_location'] ?? '') ?>"></div><div class="field"><label>Barcode (= Accession)</label><input class="input mono" name="barcode_data" value="<?= e($book['barcode_data'] ?? $book['book_id']) ?>"></div></div>
      <div class="form-row"><div class="field"><label>Purchase date</label><input class="input" type="date" name="purchase_date" value="<?= e($book['purchase_date'] ?? '') ?>"></div><div class="field"><label>Purchase price ₹</label><input class="input" type="number" step="0.01" name="purchase_price" value="<?= e($book['purchase_price'] ?? '') ?>"></div></div>
      <div class="field"><label>Supplier</label><select name="supplier_id" class="select"><option value="">— No supplier —</option><?php foreach($suppliers as $s): ?><option value="<?= (int)$s['id'] ?>" <?= (int)($book['supplier_id']??0)===(int)$s['id']?'selected':'' ?>><?= e($s['name']) ?></option><?php endforeach; ?></select></div>
      <div class="field"><label>Contents / Chapters</label><textarea class="textarea" name="contents" rows="3"><?= e($book['contents'] ?? '') ?></textarea></div>
      <div class="field"><label>Notes</label><textarea class="textarea" name="notes" rows="3"><?= e($book['notes'] ?? '') ?></textarea></div>
      <div style="display:flex; gap:8px; justify-content:flex-end">
        <button type="button" class="btn btn-ghost" onclick="document.getElementById('editPanel').style.display='none'">Cancel</button>
        <button class="btn btn-primary">Save changes</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<div class="grid-2">
  <div style="display:grid; gap:14px">
    <div class="catalog-card">
      <div class="card-inner">
        <div class="eyebrow">Bibliographic <i></i></div>
        <?php if(!empty($book['book_image_url'])): ?>
        <div style="margin-top:10px; background:white; border:1px solid var(--border); border-radius:10px; padding:8px; text-align:center">
          <img src="<?= e($book['book_image_url']) ?>" alt="Cover of <?= e($book['title']) ?>" style="max-height:220px; max-width:100%; border-radius:6px">
          <div class="mono" style="font-size:10px; letter-spacing:.12em; font-weight:700; color:var(--slate); margin-top:4px">COVER PAGE</div>
        </div>
        <?php endif; ?>
        <?php $hasCover = !empty($book['book_image_url']); if($hasCover): ?>
        <details class="bib-collapse">
          <summary><span>Catalog details</span><span class="mono"><?= e($book['publication'] ?? $book['shelf_location'] ?? '') ?></span></summary>
        <?php endif; ?>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:10px; font-size:13px">
          <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">ISBN</div><div class="mono" style="font-weight:700"><?= e($book['isbn'] ?? '—') ?></div></div>
          <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">ISSN</div><div class="mono" style="font-weight:700"><?= e($book['issn'] ?? '—') ?></div></div>
          <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">EDITION</div><div><?= e($book['edition'] ?? '—') ?></div></div>
          <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">PUBLICATION</div><div><?= e($book['publication'] ?? '—') ?></div></div>
          <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">PAGES</div><div class="mono"><?= e($book['num_pages'] ?? '—') ?></div></div>
          <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">DEPARTMENT</div><div><?= e(implode(', ', $cats ?? []) ?: ($book['category'] ?? '—')) ?></div></div>
          <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">SHELF</div><div class="mono"><?= e($book['shelf_location'] ?? '—') ?></div></div>
          <div><div style="font-size:10px; letter-spacing:.12em; color:var(--slate); font-weight:700">ACCESSION / BARCODE</div><div class="mono" style="font-weight:700"><?= e($book['book_id']) ?> · <?= e($book['barcode_data'] ?? $book['book_id']) ?></div></div>
        </div>
        <?php if($hasCover): ?></details><?php endif; ?>
        <div style="margin-top:12px; background:white; border:1px dashed var(--border); border-radius:10px; padding:8px; text-align:center">
          <img src="/barcode/render?text=<?= urlencode($book['barcode_data'] ?? $book['book_id']) ?>" alt="barcode" style="height:48px; max-width:100%">
          <div class="mono" style="font-size:10px; letter-spacing:.12em; font-weight:700"><?= e($book['barcode_data'] ?? $book['book_id']) ?></div>
        </div>
      </div>
    </div>

    <div class="catalog-card">
      <div class="card-inner">
        <div class="eyebrow">Stock & purchase <i></i></div>
        <div style="display:flex; gap:12px; margin-top:10px; align-items:center">
          <div style="font-size:28px; font-weight:900; font-family:Fraunces"><?= (int)$book['quantity_available'] ?>/<?= (int)$book['quantity_total'] ?></div>
          <div class="pill <?= $book['quantity_available']>0?'pill-green':'pill-red' ?>"><span class="pill-dot"></span><?= $book['quantity_available']>0?'on shelf':'out' ?></div>
          <span class="mono" style="font-size:11px; color:var(--slate); margin-left:auto">ACC = BARCODE</span>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:10px; font-size:12px">
          <div>Supplier: <b><?= e($book['supplier_name'] ?? '—') ?></b></div>
          <div>Price: <b class="mono">₹<?= e($book['purchase_price'] ?? '—') ?></b></div>
          <div>Date: <span class="mono"><?= e($book['purchase_date'] ?? '—') ?></span></div>
          <div>Copies: <span class="mono"><?= count($copies) ?></span></div>
        </div>
        <?php if(!empty($book['contents'])): ?><div style="margin-top:10px; background:var(--paper-2); border:1px solid var(--border); border-radius:10px; padding:10px"><div style="font-size:11px; font-weight:700; color:var(--slate)">CONTENTS</div><div style="font-size:12px; white-space:pre-wrap; margin-top:4px"><?= e($book['contents']) ?></div></div><?php endif; ?>
        <?php if(!empty($book['notes'])): ?><div style="margin-top:8px; background:var(--paper-2); border:1px solid var(--border); border-radius:10px; padding:10px"><div style="font-size:11px; font-weight:700; color:var(--slate)">NOTES</div><div style="font-size:12px; white-space:pre-wrap; margin-top:4px"><?= e($book['notes']) ?></div></div><?php endif; ?>
      </div>
    </div>
  </div>

  <div class="catalog-card">
    <div class="card-inner">
      <div class="eyebrow">Copies — each barcode is a book <i></i> <span class="mono"><?= count($copies) ?> copies</span></div>
      <div class="table-wrap" style="margin-top:10px">
        <table class="data-table"><thead><tr><th>Barcode</th><th>Status</th><th>Type</th></tr></thead><tbody>
        <?php foreach($copies as $c): ?>
          <tr><td class="mono" style="font-weight:700"><?= e($c['barcode_data'] ?? $c['accession_no']) ?></td><td><span class="pill <?= $c['status']==='available'?'pill-green':($c['status']==='issued'?'pill-amber':'pill-slate') ?>"><span class="pill-dot"></span><?= e($c['status']) ?></span></td><td><?= $c['is_reference'] ? '<span class="pill pill-amber">Reference</span>' : '<span class="pill pill-green">Issuable</span>' ?></td></tr>
        <?php endforeach; ?>
        </tbody></table>
      </div>
      <div style="margin-top:10px; display:flex; gap:8px">
        <button class="btn btn-primary btn-small" onclick="openDetailIssuePicker()">Issue book</button>
        <a href="/books" class="btn btn-ghost btn-small">Back</a>
      </div>
<!-- Detail issue picker -->
<div id="detailIssuePicker" class="modal-overlay" style="display:none">
  <div class="modal" onclick="event.stopPropagation()" style="max-width:480px">
    <div class="modal-header"><h2>Select copy to issue</h2><button class="btn btn-ghost btn-icon" onclick="closeDetailIssuePicker()" aria-label="Close"><svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg></button></div>
    <div class="modal-body">
      <div style="font-weight:800"><?= e($book['title']) ?></div>
      <div style="display:grid; gap:8px; margin-top:10px">
        <?php $availCopies = array_filter($copies, fn($c)=>$c['status']==='available');
        if(empty($availCopies)): ?><div style="text-align:center; padding:12px; color:var(--slate)">No available copies — all issued or reference.</div>
        <?php else: foreach($availCopies as $c): ?>
        <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; padding:10px 12px; background:white; border:1px solid var(--border); border-radius:12px">
          <div><div class="mono" style="font-weight:800"><?= e($c['barcode_data'] ?? $c['accession_no']) ?></div><div style="font-size:11px; color:var(--slate)">Available</div></div>
          <a href="/issue?book=<?= urlencode($c['barcode_data'] ?? $c['accession_no']) ?>" class="btn btn-primary btn-small">Issue this copy</a>
        </div>
        <?php endforeach; endif; ?>
        <?php if(!empty($availCopies)): ?><p class="mono" style="font-size:11px; color:var(--slate); margin-top:8px">Barcode = accession. Issuing is per copy, not per title.</p><?php endif; ?>
      </div>
    </div>
    <div class="modal-footer"><button class="btn btn-ghost" onclick="closeDetailIssuePicker()">Cancel</button></div>
  </div>
</div>
<script>
function openDetailIssuePicker(){ document.getElementById('detailIssuePicker').style.display='flex'; }
function closeDetailIssuePicker(){ document.getElementById('detailIssuePicker').style.display='none'; }
document.getElementById('detailIssuePicker')?.addEventListener('click', closeDetailIssuePicker);
</script>
    </div>
  </div>
</div>

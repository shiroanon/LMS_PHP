<?php $title='New Accession'; $departments = $departments ?? []; ?>
<style>
.dept-grid{display:flex; flex-wrap:wrap; gap:8px}
.dept-check{display:flex; align-items:center; gap:6px; padding:6px 12px; border:1px solid var(--border); border-radius:8px; cursor:pointer; font-size:13px !important; font-weight:400 !important; letter-spacing:0 !important; text-transform:none !important; color:var(--ink-soft) !important; background:transparent; user-select:none}
.dept-check:has(input:checked){background:var(--maroon-light); border-color:var(--maroon-border)}
.dept-check input{accent-color:var(--maroon); width:15px; height:15px; margin:0}
</style>
<div class="page-head">
  <div class="eyebrow">Catalog <i></i> new card</div>
  <h1>New <em>accession</em></h1>
  <p>One card per physical book. Title + author groups copies; accession makes it unique.</p>
</div>
<div class="catalog-card" style="max-width:720px">
  <div class="perf-notch"></div>
  <div class="card-inner">
    <form method="post" action="/books" enctype="multipart/form-data" style="display:grid; gap:12px">
      <div class="form-row"><div class="field"><label>Accession No *</label><input class="input mono" name="book_id" required placeholder="000029"></div><div class="field"><label>Barcode (optional)</label><input class="input mono" name="barcode_data" placeholder="BC-000029"></div></div>
      <div class="field"><label>Title *</label><input class="input" name="title" required></div>
      <div class="field"><label>Author *</label><input class="input" name="author" required></div>
      <div class="field"><label>ISBN</label><input class="input mono" name="isbn"></div>
      <div class="field"><label>Cover page <span style="font-weight:400; letter-spacing:0; text-transform:none; color:var(--slate)">— JPG / PNG / WebP · max 5 MB</span></label><input class="input" type="file" name="cover" accept="image/jpeg,image/png,image/webp"></div>
      <div class="field"><label>Departments</label>
        <?php if(!empty($departments)): ?>
        <div class="dept-grid">
          <input type="hidden" name="categories_present" value="1">
          <?php foreach($departments as $d): ?><label class="dept-check"><input type="checkbox" name="categories[]" value="<?= e($d) ?>"><?= e($d) ?></label><?php endforeach; ?>
        </div>
        <?php else: ?>
        <input class="input" name="category" placeholder="CSE / EC / ME">
        <?php endif; ?>
      </div>
      <div class="form-row"><div class="field"><label>Shelf</label><input class="input" name="shelf_location" placeholder="EC-3"></div><div class="field"><label>Copies for this accession</label><input class="input" type="number" name="quantity_total" value="1" min="1"></div></div>
      <button class="btn btn-primary" style="justify-content:center">Stamp accession</button>
    </form>
  </div>
</div>

<?php $title='Suppliers'; ?>
<div class="page-head" style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:end">
  <div><div class="eyebrow">Procurement <i></i> <span class="mono"><?= count($suppliers) ?> suppliers</span></div><h1>Suppliers</h1><p>Book vendors — bills link here.</p></div>
</div>
<div class="grid-2">
  <div class="catalog-card"><div class="card-inner"><div class="eyebrow">Add supplier <i></i></div>
    <form method="post" action="/suppliers" style="display:grid; gap:10px; margin-top:10px">
      <div class="field"><label>Name *</label><input class="input" name="name" required></div>
      <div class="form-row"><div class="field"><label>Shop</label><input class="input" name="shop_name"></div><div class="field"><label>Mobile *</label><input class="input mono" name="mobile" required placeholder="9876..."></div></div>
      <div class="field"><label>Location</label><input class="input" name="location"></div>
      <button class="btn btn-primary">Add vendor</button>
    </form>
  </div></div>
  <div class="table-wrap" style="align-self:start"><table class="data-table"><thead><tr><th>Name</th><th>Shop</th><th>Mobile</th><th>Bills</th></tr></thead><tbody>
    <?php foreach($suppliers as $s): ?><tr><td><b><?= e($s['name']) ?></b><br><span style="font-size:11px; color:var(--slate)"><?= e($s['location'] ?: '—') ?></span></td><td><?= e($s['shop_name'] ?: '—') ?></td><td class="mono" style="font-size:12px"><?= e($s['mobile']) ?></td><td style="text-align:center"><span class="pill pill-slate"><?= (int)$s['bills'] ?></span></td></tr><?php endforeach; if(empty($suppliers)): ?><tr><td colspan="4" style="text-align:center; color:var(--slate)">No suppliers yet.</td></tr><?php endif; ?>
  </tbody></table></div>
</div>

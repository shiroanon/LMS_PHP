<?php $title='Bills'; ?>
<div class="page-head" style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:end">
  <div><div class="eyebrow">Procurement <i></i> <span class="mono"><?= (int)($summary['cnt']??0) ?> bills · ₹<?= number_format((float)($summary['total']??0)) ?></span></div><h1>Bills</h1><p>Purchase bills by supplier.</p></div>
</div>
<div class="grid-2">
  <div class="catalog-card"><div class="card-inner"><div class="eyebrow">New bill <i></i></div>
    <form method="post" action="/bills" style="display:grid; gap:10px; margin-top:10px">
      <div class="form-row"><div class="field"><label>Bill no *</label><input class="input mono" name="bill_no" required placeholder="BILL-2026-001"></div><div class="field"><label>Supplier *</label><select name="supplier_id" class="select" required><option value="">— Select —</option><?php foreach($suppliers as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select></div></div>
      <div class="form-row"><div class="field"><label>Date</label><input class="input" type="date" name="purchase_date" value="<?= date('Y-m-d') ?>"></div><div class="field"><label>Total ₹ *</label><input class="input mono" type="number" step="0.01" name="total_amount" required></div></div>
      <div class="field"><label>Payment</label><select name="payment_mode" class="select"><option value="cash">cash</option><option value="cheque">cheque</option><option value="online">online</option><option value="credit">credit</option></select></div>
      <button class="btn btn-primary">Add bill</button>
    </form>
  </div></div>
  <div class="table-wrap" style="align-self:start"><table class="data-table"><thead><tr><th>Bill</th><th>Supplier</th><th>Date</th><th>Total</th><th></th></tr></thead><tbody>
    <?php foreach($bills as $b): ?><tr><td class="mono" style="font-weight:700"><?= e($b['bill_no']) ?></td><td><?= e($b['supplier_name']) ?></td><td class="mono" style="font-size:12px"><?= e($b['purchase_date']) ?></td><td class="mono" style="font-weight:700">₹<?= number_format($b['total_amount']) ?></td><td><a href="/bills/<?= (int)$b['id'] ?>" class="btn btn-ghost btn-small">View →</a></td></tr><?php endforeach; if(empty($bills)): ?><tr><td colspan="5" style="text-align:center; color:var(--slate)">No bills yet — add first vendor bill.</td></tr><?php endif; ?>
  </tbody></table></div>
</div>

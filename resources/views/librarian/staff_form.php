<?php $title='New Staff'; ?>
<div class="page-head"><div class="eyebrow">People <i></i> new card</div><h1>New <em>staff</em></h1><p>Library ID is the barcode — same as CSV import.</p></div>
<div class="catalog-card" style="max-width:640px"><div class="perf-notch"></div><div class="card-inner">
  <form method="post" action="/staff" style="display:grid; gap:12px">
    <div class="field"><label>Name *</label><input class="input" name="name" required></div>
    <div class="field"><label>Staff ID</label><input class="input mono" name="library_id" placeholder="S019"></div>
    <div class="form-row"><div class="field"><label>Department</label><input class="input" name="loan_category" placeholder="Library Department"></div><div class="field"><label>Membership</label><input class="input" name="membership_type" placeholder="Enternal Staff"></div></div>
    <div class="field"><label>Status</label><select name="status" class="select"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
    <button class="btn btn-primary" style="justify-content:center">Create staff</button>
  </form>
</div></div>

<?php $title='New Borrower'; ?>
<div class="page-head"><div class="eyebrow">Borrowers <i></i> new card</div><h1>New <em>borrower</em></h1><p>Enrollment is the login. Initial password = enrollment. Library ID only when the card is actually issued.</p></div>
<div class="catalog-card" style="max-width:640px"><div class="perf-notch"></div><div class="card-inner">
  <form method="post" action="/students" style="display:grid; gap:12px">
    <div class="form-row"><div class="field"><label>Enrollment *</label><input class="input mono" name="enrollment_no" required placeholder="0901CS221043"></div><div class="field"><label>Library ID</label><input class="input mono" name="library_id" placeholder="leave blank if none yet"></div></div>
    <div class="field"><label>Name *</label><input class="input" name="name" required></div>
    <div class="form-row"><div class="field"><label>Branch *</label><input class="input" name="branch" required placeholder="CSE"></div><div class="field"><label>Year</label><select name="year" class="select"><option>1</option><option>2</option><option>3</option><option>4</option></select></div></div>
    <div class="form-row"><div class="field"><label>Contact</label><input class="input" name="contact"></div><div class="field"><label>Email</label><input class="input" name="email" type="email"></div></div>
    <button class="btn btn-primary" style="justify-content:center">Create borrower</button>
  </form>
</div></div>

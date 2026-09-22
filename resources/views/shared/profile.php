<?php $title='Profile'; $must = (int)(($user['must_change_password'] ?? $profile['must_change_password'] ?? 0)); ?>
<div class="catalog-card" style="max-width:560px; margin:40px auto"><div class="card-inner"><div class="stamp">Librarian</div><h1 class="display" style="margin:10px 0 6px"><?= e($profile['display_name'] ?? $profile['username']) ?></h1><p style="color:var(--slate)">Role: <?= e($profile['role']) ?> · Username: <span class="mono"><?= e($profile['username']) ?></span></p>
<?php if($must): ?><div style="background:var(--warning-bg); border:1px solid #FBE8B5; color:var(--warning); padding:10px 12px; border-radius:12px; font-size:13px; font-weight:600; margin:12px 0">First login — set a new password below to unlock the desk.</div><?php endif; ?>
<div style="height:1px; background:var(--border); margin:12px 0"></div>
<div class="eyebrow">Change password <i></i></div>
<form method="post" action="/profile" style="display:grid; gap:12px; margin-top:12px">
  <?php if(!$must): ?><div class="field"><label>Current password</label><input type="password" name="current_password" class="input"></div><?php endif; ?>
  <div class="field"><label>New password (min 4 characters)</label><input type="password" name="new_password" class="input" required minlength="4"></div>
  <button class="btn btn-primary" style="justify-content:center">Save new password</button>
</form>
</div></div>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign in — RJIT Central Library</title>
<link rel="stylesheet" href="/assets/css/app.css">
<style>
.login-wrap{
  min-height:100vh; display:grid; place-items:center; padding:32px 20px;
  background:
    radial-gradient(900px 500px at 50% -10%, #FDF0D9 0%, transparent 60%),
    radial-gradient(800px 600px at 100% 100%, #E8D9B0 0%, transparent 55%),
    var(--paper);
}
.login-stack{ width:min(420px,100%); display:grid; gap:18px }
.brand-center{ display:flex; flex-direction:column; align-items:center; text-align:center; gap:10px }
.form-card{
  background:var(--paper-3); border:1px solid var(--border); border-radius:20px; box-shadow:var(--shadow-card); padding:26px;
  position:relative; overflow:hidden;
}
.form-card::before{content:""; position:absolute; left:0; right:0; top:0; height:8px; background:linear-gradient(90deg, var(--maroon), var(--brass))}
</style>
</head>
<body>
<div class="login-wrap">
  <div class="login-stack">
    <div class="brand-center">
      <img src="/assets/img/logo.png" alt="RJIT" style="width:56px;height:56px;object-fit:contain;background:white;border-radius:12px;padding:6px;border:1px solid var(--border);box-shadow:var(--shadow-card)">
      <div>
        <div style="font-family:Fraunces; font-weight:900; font-size:20px; line-height:1">RJIT Central Library</div>
        <div style="font-size:11px; letter-spacing:.14em; text-transform:uppercase; color:var(--slate); font-weight:700">Gwalior · Est. 1999</div>
      </div>
    </div>
    <form method="post" action="/login" class="form-card" style="width:100%">
      <div class="eyebrow" style="margin-bottom:8px">Sign in to the ledger <i></i></div>
      <h2 class="display" style="font-size:26px; margin:0 0 6px">Welcome back</h2>
      <p style="color:var(--slate); font-size:13px; margin:0 0 18px">Librarian or student — same desk, same cards.</p>
      <?php if(!empty($error)): ?><div style="background:var(--vermillion-bg); border:1px solid var(--maroon-border); color:var(--maroon); padding:10px 12px; border-radius:12px; font-size:13px; margin-bottom:12px"><?= e($error) ?></div><?php endif; ?>
      <div class="field"><label>Library ID — students / username — librarians</label><input class="input mono" type="text" name="username" required placeholder="e.g. A20028082 or admin" value="<?= e($_POST['username'] ?? '') ?>"></div>
      <div class="field"><label>Password</label><input class="input" type="password" name="password" required placeholder="••••••••"></div>
      <div style="font-size:11px; color:var(--slate); background:var(--paper-2); border:1px solid var(--border); padding:10px 12px; border-radius:12px">Student hint: sign in with your <b>library ID</b> (not enrollment). First-timers use the enrollment as the initial password. You’ll be asked to change it.</div>
      <button class="btn btn-primary" style="width:100%; justify-content:center; margin-top:10px; padding:12px">Sign in — open the ledger</button>
    </form>
    <div style="text-align:center; font-size:11px; color:var(--slate)">© RJIT · LMS · 2026</div>
  </div>
</div>
</body>
</html>

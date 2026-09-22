<?php
use App\Core\Auth;
$user = Auth::user();
$flashSuccess = flash('success');
$flashError = flash('error');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($title) ? e($title).' — ' : '' ?>RJIT Central Library</title>
<link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<?php if($user): ?>
<div class="shell">
  <?php include dirname(__DIR__) . '/partials/sidebar.php'; ?>
  <div class="main">
    <?php include dirname(__DIR__) . '/partials/topbar.php'; ?>
    <div class="page">
      <?php if($flashSuccess): ?><div data-flash style="margin-bottom:14px; padding:12px 14px; background:var(--success-bg); border:1px solid #CFF0E3; color:var(--success); border-radius:12px; font-size:13px; font-weight:600"><?= e($flashSuccess) ?></div><?php endif; ?>
      <?php if($flashError): ?><div data-flash style="margin-bottom:14px; padding:12px 14px; background:var(--vermillion-bg); border:1px solid var(--maroon-border); color:var(--maroon); border-radius:12px; font-size:13px; font-weight:600"><?= e($flashError) ?></div><?php endif; ?>
      <?= $content ?? '' ?>
    </div>
  </div>
</div>
<?php else: ?>
  <?= $content ?? '' ?>
<?php endif; ?>
<script src="/assets/js/app.js"></script>
</body>
</html>

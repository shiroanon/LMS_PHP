<?php
use App\Core\Auth;
$user = Auth::user();
$role = $user['role'] ?? '';
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
function active(string $p): string {
  $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
  return $uri === $p || str_starts_with($uri, $p.'/') || ($p==='/' && $uri==='/') ? 'active' : '';
}
function svg(string $name): string {
  $icons = [
    'dash'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
    'users'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'book'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
    'issue'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>',
    'return'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 14L4 9l5-5"/><path d="M4 9h10.5A2.5 2.5 0 0 1 17 11.5v7.5"/></svg>',
    'scan'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><line x1="7" y1="12" x2="17" y2="12"/></svg>',
    'barcode'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 5v14"/><path d="M6 5v14"/><path d="M9 5v14"/><path d="M12 5v14"/><path d="M15 5v14"/><path d="M18 5v14"/><path d="M21 5v14"/></svg>',
    'money'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
    'shop'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 9l1-5h16l1 5"/><path d="M4 9h16v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9z"/><path d="M9 13a3 3 0 1 0 6 0"/></svg>',
    'bill'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
    'visits'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>',
    'holiday'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
    'bell'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 8A6 6 0 0 0 6 8c0 7-6 9-6 9h18s-6-2-6-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>',
    'chat'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
    'library'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 7v14"/><path d="M16 7h.01"/><path d="M8 7h.01"/><path d="M4 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7z"/></svg>',
    'report'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
    'rule'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
    'backup'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>',
    'cal'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
    'suggest'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
  ];
  return $icons[$name] ?? '';
}
$librarianSections = [
  ['title'=>'Core','items'=>[
    ['/dashboard','Dashboard','dash'],
    ['/students','Students','users'],
    ['/staff','Staff','users'],
    ['/books','Books','book'],
  ]],
  ['title'=>'Operations','items'=>[
    ['/issue','Issue','issue'],
    ['/return','Return','return'],
    ['/scanner','Scanner','scan'],
    ['/barcodes','Barcodes','barcode'],
    ['/reservations','Reservations','cal'],
  ]],
  ['title'=>'Management','items'=>[
    ['/fines','Fines','money'],
    ['/suppliers','Suppliers','shop'],
    ['/bills','Bills','bill'],
    ['/visits','Visits','visits'],
    ['/holidays','Holidays','holiday'],
    ['/disposed','Disposed','book'],
  ]],
  ['title'=>'Communication','items'=>[
    ['/notifications','Notifications','bell'],
    ['/feedback','Feedback','chat'],
    ['/suggestions','Suggestions','suggest'],
    ['/digital-library','Digital Library','library'],
  ]],
  ['title'=>'System','items'=>[
    ['/reports','Reports','report'],
    ['/rules','Rules','rule'],
    ['/backup','Backup','backup'],
  ]],
];
$studentSections = [
  ['title'=>'Core','items'=>[
    ['/dashboard','Dashboard','dash'],
    ['/my-books','My Books','book'],
    ['/reservations','Reservations','cal'],
    ['/profile','Profile','users'],
  ]],
  ['title'=>'Resources','items'=>[
    ['/books','Library Books','library'],
    ['/digital-library','Digital Library','library'],
    ['/suggestions','Suggestions','suggest'],
  ]],
];
$sections = $role==='librarian' ? $librarianSections : $studentSections;
?>
<aside class="sidebar" id="sidebar">
  <div class="brand">
    <img src="/assets/img/logo.png" alt="RJIT" style="width:44px;height:44px;object-fit:contain;flex-shrink:0;background:white;border-radius:8px;padding:3px;border:1px solid var(--border)">
    <div>
      <div class="brand-name">RJIT Central Library</div>
      <div class="brand-sub">RJIT · Gwalior · Est. 1999</div>
    </div>
  </div>
  <div class="rail-rod"></div>
  <nav class="nav">
    <?php foreach($sections as $sec): ?>
      <div class="nav-section">
        <div class="nav-title"><?= e($sec['title']) ?></div>
        <?php foreach($sec['items'] as [$path,$label,$icon]): ?>
          <a class="nav-link <?= active($path) ?>" href="<?= e($path) ?>">
            <?= svg($icon) ?><span><?= e($label) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-foot">
    <div class="avatar"><?= $role==='librarian' ? '◆' : '◎' ?></div>
    <div class="meta">
      <strong><?= e($user['display_name'] ?? $user['username'] ?? '—') ?></strong>
      <span><?= e($role) ?> · <?= e($user['username'] ?? '') ?></span>
    </div>
    <form method="post" action="/logout" style="margin:0">
      <button class="btn-logout" title="Sign out">↗</button>
    </form>
  </div>
</aside>

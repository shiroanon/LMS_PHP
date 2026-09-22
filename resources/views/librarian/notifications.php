<?php $title='Notifications'; ?>
<div class="page-head"><div class="eyebrow">Comms <i></i> <?= count($logs) ?> in log</div><h1>Notifications</h1><p>Templates + log. SMTP via env still optional — logs always.</p></div>
<div class="grid-2">
  <div class="catalog-card"><div class="card-inner"><div class="eyebrow">Send <i></i></div>
    <form method="post" action="/notifications/send" style="display:grid; gap:10px; margin-top:10px">
      <div class="field"><label>Branch filter (optional)</label><select name="branch" class="select"><option value="">All branches</option><?php foreach($branches as $b): ?><option value="<?= e($b) ?>"><?= e($b) ?></option><?php endforeach; ?></select></div>
      <div class="field"><label>Subject</label><input class="input" name="subject" placeholder="Library update" required></div>
      <div class="field"><label>Message — use {student_name} {book_title} {due_date}</label><textarea class="textarea" name="body" required placeholder="Dear {student_name}, ..."></textarea></div>
      <button class="btn btn-primary">Log &amp; send (if SMTP)</button>
    </form>
    <div style="margin-top:12px; display:grid; gap:8px">
      <div class="eyebrow">Templates <i></i></div>
      <?php foreach($templates as $t): ?><div style="padding:10px 12px; background:var(--paper-2); border:1px solid var(--border); border-radius:12px"><b style="font-size:13px"><?= e($t['name']) ?></b><div class="mono" style="font-size:11px; color:var(--slate)">Subject: <?= e($t['subject']) ?></div><div style="font-size:12px; color:var(--slate); margin-top:4px; white-space:pre-wrap"><?= e($t['body']) ?></div></div><?php endforeach; ?>
    </div>
  </div></div>
  <div class="table-wrap" style="align-self:start; max-height:720px; overflow:auto"><table class="data-table"><thead><tr><th>Time</th><th>Student</th><th>Message</th></tr></thead><tbody>
    <?php foreach($logs as $l): ?><tr><td class="mono" style="font-size:11px"><?= e(substr($l['created_at'],0,16)) ?></td><td><b style="font-size:12px"><?= e($l['name']) ?></b><br><span class="mono" style="font-size:11px; color:var(--slate)"><?= e($l['enrollment_no']) ?></span></td><td style="font-size:12px; max-width:320px; white-space:pre-wrap; word-break:break-word"><?= e(mb_strimwidth($l['message'],0,140,'…')) ?></td></tr><?php endforeach; if(empty($logs)): ?><tr><td colspan="3" style="text-align:center; color:var(--slate)">No notifications logged.</td></tr><?php endif; ?>
  </tbody></table></div>
</div>

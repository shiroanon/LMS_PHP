<?php $title='Rules'; ?>
<div class="page-head"><div class="eyebrow">Policy <i></i> <?= count($rules) ?> rules</div><h1>Library <em>rules</em></h1><p>Sorted by the desk — printed on the due slip.</p></div>
<div class="grid-2">
  <div class="catalog-card"><div class="card-inner"><div class="eyebrow">Add rule <i></i></div>
    <form method="post" action="/rules" style="display:grid; gap:10px; margin-top:10px">
      <div class="field"><label>Rule text</label><textarea class="textarea" name="rule_text" required placeholder="Return books within 14 days..."></textarea></div>
      <button class="btn btn-primary">Add rule</button>
    </form>
  </div></div>
  <div class="catalog-card" style="align-self:start"><div class="card-inner"><div class="eyebrow">Current rules <i></i></div>
    <ol style="display:grid; gap:10px; margin:12px 0 0; padding:0; list-style:none">
      <?php foreach($rules as $i=>$r): ?><li style="display:flex; gap:10px; padding:10px 12px; background:var(--paper-2); border:1px solid var(--border); border-radius:12px"><span class="mono" style="font-weight:700; color:var(--maroon)"><?= $i+1 ?>.</span><span style="flex:1; font-size:13px"><?= e($r['rule_text']) ?></span><form method="post" action="/rules/<?= (int)$r['id'] ?>/delete"><button class="btn btn-ghost btn-small" style="color:var(--vermillion)" onclick="return confirm('Remove?')">✕</button></form></li><?php endforeach; if(empty($rules)): ?><li style="color:var(--slate); text-align:center">No rules yet.</li><?php endif; ?>
    </ol>
  </div></div>
</div>

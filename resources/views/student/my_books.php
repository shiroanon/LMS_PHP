<?php $title='My Books'; ?>
<div class="page-head"><div class="eyebrow">Shelf <i></i> my loans</div><h1>My <em>books</em></h1><p>Issued · overdue · returned — your due slips.</p></div>
<div class="table-wrap"><table class="data-table"><thead><tr><th>Book</th><th>Accession</th><th>Issued</th><th>Due</th><th>Status</th></tr></thead><tbody>
<?php foreach(($issues ?? []) as $r): ?><tr><td><b><?= e($r['title']) ?></b></td><td class="mono" style="font-size:11px"><?= e($r['accession_no'] ?? '') ?></td><td class="mono" style="font-size:12px"><?= e($r['issue_date']) ?></td><td class="mono" style="font-size:12px"><?= e($r['due_date']) ?></td><td><span class="pill <?= $r['status']==='overdue'?'pill-red':($r['status']==='issued'?'pill-green':'pill-slate') ?>"><?= e($r['status']) ?></span></td></tr><?php endforeach; if(empty($issues)): ?><tr><td colspan="5" style="text-align:center; color:var(--slate); padding:20px">No books on loan.</td></tr><?php endif; ?>
</tbody></table></div>

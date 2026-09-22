<?php $title='Reports';
/* Ledger-desk icon set — same 24px ink-stroke vernacular as sidebar nav,
   presented as accession stamps (see <style> below).
   NOTE: array lives INSIDE the function (like sidebar's svg()) because
   View::render() includes this file in method scope, so `global` won't see it. */
function rep_icon(string $n): string {
  static $icons = null;
  if ($icons === null) $icons = [
  'issue'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>',
  'return'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 14L4 9l5-5"/><path d="M4 9h10.5A2.5 2.5 0 0 1 17 11.5v7.5"/></svg>',
  'clock'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
  'list'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>',
  'money'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
  'bill'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
  'shop'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l1-5h16l1 5"/><path d="M4 9h16v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9z"/><path d="M9 13a3 3 0 1 0 6 0"/></svg>',
  'visits'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>',
  'book'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
  'users'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
  'download'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>',
  'file'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
  'chart'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
  'search'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
  ];
  return $icons[$n] ?? '';
}
?>
<style>
/* Signature: accession-stamp medallion. One risk, quiet surroundings —
   ink stroke, maroon rubber ring, brass catalog-hole dot, mono ledger tag. */
.rep-stamp{width:54px;height:54px;border-radius:50%;display:grid;place-items:center;margin:2px auto 12px;
  background:var(--paper-3);color:var(--ink);
  border:1.5px solid var(--maroon-border);outline:1px dashed var(--border-strong);outline-offset:3px;
  transform:rotate(-4deg);transition:transform .18s ease,border-color .18s ease,color .18s ease,box-shadow .18s ease;
  position:relative}
.rep-stamp svg{width:22px;height:22px}
.rep-stamp::after{content:"";position:absolute;top:-5px;right:0;width:10px;height:10px;border-radius:50%;
  background:var(--brass);border:2px solid var(--brass-soft);box-shadow:inset 0 1px 2px rgba(0,0,0,.25)}
.catalog-card:hover .rep-stamp,.catalog-card:focus-within .rep-stamp{transform:rotate(0deg);border-color:var(--maroon);color:var(--maroon);box-shadow:var(--shadow-stamp)}
.rep-tag{font-family:"JetBrains Mono",ui-monospace,monospace;font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;
  color:var(--slate);display:inline-flex;align-items:center;gap:6px;margin-bottom:6px}
.rep-tag i{width:6px;height:6px;border-radius:50%;background:var(--dot,var(--brass));display:inline-block;flex:none}
.rep-title{font-family:"Fraunces",Georgia,serif;font-weight:800;font-size:16px;line-height:1.2;letter-spacing:-.01em;color:var(--ink);margin:0 0 2px}
.rep-sub{font-size:12px;color:var(--slate);margin-bottom:12px}
.rep-sec{font-family:"Fraunces",Georgia,serif;font-weight:800;font-size:15px;margin:0 0 4px}
.rep-secsub{font-family:"JetBrains Mono",ui-monospace,monospace;font-size:10.5px;letter-spacing:.1em;color:var(--slate);margin-bottom:12px;text-transform:uppercase}
.tab{display:inline-flex;align-items:center}
.tab svg{width:14px;height:14px}
.tab:focus-visible,.btn:focus-visible{outline:2px solid var(--maroon);outline-offset:2px}
.btn svg{width:14px;height:14px;flex:none}
.empty-stamp{width:46px;height:46px;border-radius:50%;display:grid;place-items:center;margin:0 auto;
  background:var(--paper-2);border:1.5px solid var(--border-strong);outline:1px dashed var(--border-strong);outline-offset:3px;color:var(--slate)}
.empty-stamp svg{width:20px;height:20px}
@media (prefers-reduced-motion:reduce){.rep-stamp{transition:none;transform:none}}
</style>
<div class="page-head">
  <div class="eyebrow">Ledger <i></i> reports & analytics</div>
  <h1>Reports <em>& analytics</em></h1>
  <p>Export sheets, PDFs and custom builder — barcode-aware.</p>
</div>

<div class="tabs">
  <button class="tab active" data-tab="export" onclick="switchTab('export')"><?= rep_icon('download') ?>Export Reports</button>
  <button class="tab" data-tab="analytics" onclick="switchTab('analytics')"><?= rep_icon('chart') ?>Analytics</button>
  <button class="tab" data-tab="custom" onclick="switchTab('custom')"><?= rep_icon('search') ?>Custom Builder</button>
</div>

<!-- EXPORT TAB -->
<div id="tab-export">
  <h3 class="rep-sec">Excel Sheets</h3>
  <div class="rep-secsub">XLS · 8 ledgers · barcode-aware</div>
  <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:14px; margin-bottom:22px">
    <?php
    $exports=[
      ['key'=>'issued','label'=>'Active Issues','icon'=>'issue','desc'=>'Currently issued','tag'=>'XLS · Active','url'=>'/reports/export?type=issued','color'=>'#8B2D3B'],
      ['key'=>'returned','label'=>'Returned Books','icon'=>'return','desc'=>'Returned records','tag'=>'XLS · Returned','url'=>'/reports/export?type=returned','color'=>'#10805A'],
      ['key'=>'overdue','label'=>'Overdue Books','icon'=>'clock','desc'=>'Past due date','tag'=>'XLS · Overdue','url'=>'/reports/export?type=overdue','color'=>'#E84855'],
      ['key'=>'all-issues','label'=>'All Issues','icon'=>'list','desc'=>'Full history','tag'=>'XLS · Ledger','url'=>'/reports/export?type=all-issues','color'=>'#677387'],
      ['key'=>'fines','label'=>'Fine Report','icon'=>'money','desc'=>'Fines + status','tag'=>'XLS · Fines','url'=>'/reports/export?type=fines','color'=>'#B58500'],
      ['key'=>'bills','label'=>'Purchase Bills','icon'=>'bill','desc'=>'Book purchase bills','tag'=>'XLS · Bills','url'=>'/reports/export?type=bills','color'=>'#A14B59'],
      ['key'=>'suppliers','label'=>'Suppliers Report','icon'=>'shop','desc'=>'Vendors & stats','tag'=>'XLS · Vendors','url'=>'/reports/export?type=suppliers','color'=>'#8B2D3B'],
      ['key'=>'visits','label'=>'Entrance Visits','icon'=>'visits','desc'=>'Gate logs','tag'=>'XLS · Gate','url'=>'/reports/export?type=visits','color'=>'#1a7f64'],
    ];
    foreach($exports as $r): ?>
    <div class="catalog-card"><div class="card-inner" style="text-align:center; padding:18px">
      <div class="rep-stamp" aria-hidden="true"><?= rep_icon($r['icon']) ?></div>
      <div class="rep-tag"><i style="--dot:<?= $r['color'] ?>"></i><?= e($r['tag']) ?></div>
      <div class="rep-title"><?= e($r['label']) ?></div>
      <div class="rep-sub"><?= e($r['desc']) ?></div>
      <a class="btn btn-primary" href="<?= e($r['url']) ?>" style="width:100%; justify-content:center" aria-label="Download <?= e($r['label']) ?> as Excel"><?= rep_icon('download') ?>Download Excel</a>
    </div></div>
    <?php endforeach; ?>
  </div>

  <h3 class="rep-sec">PDF Documents</h3>
  <div class="rep-secsub">PDF · 8 dossiers · stamped for circulation</div>
  <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:14px">
    <?php
    $pdfs=[
      ['key'=>'books','label'=>'Books Inventory','icon'=>'book','desc'=>'Stock details','tag'=>'PDF · Stock'],
      ['key'=>'students','label'=>'Students List','icon'=>'users','desc'=>'All student records','tag'=>'PDF · Roll'],
      ['key'=>'issues','label'=>'Issues History','icon'=>'issue','desc'=>'Issue/return history','tag'=>'PDF · Ledger'],
      ['key'=>'fines','label'=>'Fines Summary','icon'=>'money','desc'=>'Fines + status','tag'=>'PDF · Fines'],
      ['key'=>'overdue','label'=>'Overdue Books','icon'=>'clock','desc'=>'Past due','tag'=>'PDF · Overdue'],
      ['key'=>'visits','label'=>'Library Attendance','icon'=>'visits','desc'=>'Gate logs','tag'=>'PDF · Gate'],
      ['key'=>'suppliers','label'=>'Suppliers Report','icon'=>'shop','desc'=>'Vendors','tag'=>'PDF · Vendors'],
      ['key'=>'bills','label'=>'Purchase Bills','icon'=>'bill','desc'=>'Bills','tag'=>'PDF · Bills'],
    ];
    foreach($pdfs as $r): ?>
    <div class="catalog-card"><div class="card-inner" style="text-align:center; padding:18px">
      <div class="rep-stamp" aria-hidden="true"><?= rep_icon($r['icon']) ?></div>
      <div class="rep-tag"><i></i><?= e($r['tag']) ?></div>
      <div class="rep-title" style="font-size:15px"><?= e($r['label']) ?></div>
      <div class="rep-sub"><?= e($r['desc']) ?></div>
      <a class="btn btn-ghost btn-small" href="/reports/pdf?type=<?= e($r['key']) ?>" target="_blank" style="width:100%; justify-content:center" aria-label="Open <?= e($r['label']) ?> as PDF"><?= rep_icon('file') ?>Download PDF</a>
    </div></div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ANALYTICS TAB -->
<div id="tab-analytics" style="display:none">
  <div style="display:grid; gap:14px">
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px">
      <div class="catalog-card"><div class="card-inner">
        <div class="eyebrow">Students branch-wise <i></i></div>
        <div style="display:grid; gap:8px; margin-top:10px">
          <?php $max = max(array_column($branch_stats,'cnt') ?: [1]); foreach($branch_stats as $b): $w=($b['cnt']/$max)*100; ?>
          <div style="display:flex; align-items:center; gap:10px">
            <span class="pill pill-slate" style="width:90px; justify-content:center; flex-shrink:0"><?= e($b['branch']) ?></span>
            <div style="flex:1; background:var(--paper-2); border-radius:999px; height:16px; overflow:hidden"><div style="height:100%; background:var(--maroon); width:<?= $w ?>%"></div></div>
            <span class="mono" style="font-weight:700; width:30px; text-align:right"><?= (int)$b['cnt'] ?></span>
          </div>
          <?php endforeach; if(empty($branch_stats)): ?><div style="color:var(--slate); font-size:13px">No data</div><?php endif; ?>
        </div>
      </div></div>
      <div class="catalog-card"><div class="card-inner">
        <div class="eyebrow">Fines branch-wise <i></i></div>
        <?php if(empty($fine_branch)): ?><div style="color:var(--slate); font-size:13px">No fine data</div>
        <?php else: ?>
        <div class="table-wrap" style="margin-top:10px"><table class="data-table" style="font-size:12px"><thead><tr><th>Branch</th><th>Total</th><th>Paid</th><th>Pending</th></tr></thead><tbody>
          <?php foreach($fine_branch as $f): ?><tr><td><span class="pill pill-slate"><?= e($f['branch']) ?></span></td><td class="mono">₹<?= number_format($f['total_amount']??0) ?></td><td class="mono" style="color:var(--success)">₹<?= number_format($f['paid_amount']??0) ?></td><td class="mono" style="color:var(--vermillion)">₹<?= number_format($f['pending_amount']??0) ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
        <?php endif; ?>
      </div></div>
    </div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px">
      <div class="catalog-card"><div class="card-inner">
        <div class="eyebrow">Monthly purchases <i></i></div>
        <?php if(empty($purchase_stats)): ?><div style="color:var(--slate); font-size:13px; margin-top:8px">No purchase data</div>
        <?php else: foreach($purchase_stats as $p): ?>
        <div style="display:flex; justify-content:space-between; padding:8px 10px; background:var(--paper-2); border:1px solid var(--border); border-radius:10px; margin-top:8px; font-size:13px">
          <span class="mono"><?= e($p['month']) ?></span><span> <b style="color:var(--success)">₹<?= number_format($p['total_spent']) ?></b> <span style="color:var(--slate)"><?= (int)$p['bills_count'] ?> bills</span></span>
        </div>
        <?php endforeach; endif; ?>
      </div></div>
      <div class="catalog-card"><div class="card-inner">
        <div class="eyebrow">Visits branch-wise <i></i></div>
        <?php if(empty($visit_stats)): ?><div style="color:var(--slate); font-size:13px; margin-top:8px">No visit data</div>
        <?php else: $vmax=max(array_column($visit_stats,'cnt')?:[1]); foreach($visit_stats as $v): $w=($v['cnt']/$vmax)*100; ?>
        <div style="display:flex; align-items:center; gap:10px; margin-top:8px">
          <span class="pill pill-slate" style="width:90px; justify-content:center"><?= e($v['branch']) ?></span>
          <div style="flex:1; background:var(--paper-2); border-radius:999px; height:16px; overflow:hidden"><div style="height:100%; background:var(--brass); width:<?= $w ?>%"></div></div>
          <span class="mono" style="font-weight:700; width:30px; text-align:right"><?= (int)$v['cnt'] ?></span>
        </div>
        <?php endforeach; endif; ?>
      </div></div>
    </div>
  </div>
</div>

<!-- CUSTOM TAB -->
<div id="tab-custom" style="display:none">
  <div class="catalog-card"><div class="card-inner">
    <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:end">
      <div class="field" style="min-width:220px">
        <label>Data entity</label>
        <select id="reportType" class="select" style="border-left:3px solid var(--maroon)" onchange="onTypeChange()">
          <option value="issues">Issues History</option>
          <option value="fines">Fines Summary</option>
          <option value="students">Students List</option>
          <option value="books">Books Inventory</option>
          <option value="visits">Library Attendance</option>
          <option value="suppliers">Suppliers</option>
          <option value="bills">Purchase Bills</option>
        </select>
      </div>
      <div style="display:flex; gap:6px; flex-wrap:wrap; align-items:center">
        <span style="font-size:11px; color:var(--slate); font-weight:700">Quick range:</span>
        <?php foreach(['today','yesterday','last7','last30','thisWeek','thisMonth'] as $k): ?><button class="btn btn-ghost btn-small" onclick="applyPreset('<?= $k ?>')"><?= $k ?></button><?php endforeach; ?>
        <select id="group_by" class="select" style="width:150px"><option value="">No grouping</option><option value="daily">Daily</option><option value="weekly">Weekly</option><option value="monthly">Monthly</option></select>
      </div>
    </div>

    <div id="filterGrid" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:12px; margin-top:14px">
      <div class="field"><label>Search</label><input id="f_q" class="input" placeholder="Name, ID, title..." data-barcode-input></div>
      <div class="field" id="wrap_member_type"><label>Member type</label><select id="f_member_type" class="select"><option value="all">All</option><option value="student">Student</option><option value="staff">Staff</option></select></div>
      <div class="field" id="wrap_book_code"><label>Book accession (scan)</label><input id="f_book_code" class="input mono" placeholder="000029" data-barcode-input></div>
      <div class="field" id="wrap_member_id"><label>Member ID (scan)</label><div style="display:flex; gap:6px"><input id="f_member_id" class="input mono" placeholder="S001 / enrollment" data-barcode-input style="flex:1"><button class="btn btn-ghost btn-small" onclick="document.getElementById('f_member_id').value=''">×</button></div></div>
      <div class="field" id="wrap_start"><label>From</label><input id="f_start" type="date" class="input"></div>
      <div class="field" id="wrap_end"><label>To</label><input id="f_end" type="date" class="input"></div>
      <div class="field" id="wrap_branch"><label>Branch</label><select id="f_branch" class="select"><option value="">All Branches</option><?php foreach($branches as $b): ?><option value="<?= e($b) ?>"><?= e($b) ?></option><?php endforeach; ?></select></div>
      <div class="field" id="wrap_year"><label>Year</label><select id="f_year" class="select"><option value="">All</option><option value="1">1 yr</option><option value="2">2 yr</option><option value="3">3 yr</option><option value="4">4 yr</option></select></div>
      <div class="field" id="wrap_has_fines"><label>Fines filter</label><select id="f_has_fines" class="select"><option value="all">All</option><option value="true">With pending fines</option></select></div>
      <div class="field" id="wrap_category"><label>Category</label><select id="f_category" class="select"><option value="">All</option><?php foreach($categories as $c): ?><option value="<?= e($c) ?>"><?= e($c) ?></option><?php endforeach; ?></select></div>
      <div class="field" id="wrap_availability"><label>Availability</label><select id="f_availability" class="select"><option value="all">All</option><option value="available">Available</option><option value="out_of_stock">Out of stock</option></select></div>
      <div class="field" id="wrap_supplier"><label>Supplier</label><select id="f_supplier" class="select"><option value="">All</option><?php foreach($suppliers as $s): ?><option value="<?= e($s['id']) ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select></div>
      <div class="field" id="wrap_has_bills"><label>Purchase history</label><select id="f_has_bills" class="select"><option value="all">All</option><option value="true">With bills</option><option value="false">Without bills</option></select></div>
      <div class="field" id="wrap_status"><label>Status</label><select id="f_status" class="select"><option value="">All</option><option value="issued">Issued</option><option value="returned">Returned</option><option value="overdue">Overdue</option></select></div>
      <div class="field" id="wrap_status_fines"><label>Fine status</label><select id="f_status_fines" class="select"><option value="">All</option><option value="pending">Pending</option><option value="paid">Paid</option></select></div>
      <div class="field" id="wrap_payment"><label>Payment</label><select id="f_payment" class="select"><option value="">All</option><option value="cash">Cash</option><option value="cheque">Cheque</option><option value="online">Online</option><option value="credit">Credit</option></select></div>
    </div>

    <div style="display:flex; gap:10px; margin-top:14px; flex-wrap:wrap">
      <button class="btn btn-primary" onclick="buildReport()"><?= rep_icon('search') ?>Build report</button>
      <button class="btn btn-ghost" id="btnExcel" onclick="downloadExcel()" style="display:none"><?= rep_icon('download') ?>Excel (<span id="excelCount">0</span>)</button>
      <button class="btn btn-ghost" id="btnPdf" onclick="downloadPdf()" style="display:none"><?= rep_icon('file') ?>PDF</button>
    </div>

    <div id="reportResult" style="margin-top:16px; overflow:auto; display:none">
      <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--slate); margin-bottom:6px"><span id="reportMeta"></span><span id="reportBadge" class="pill pill-green" style="display:none"></span></div>
      <div class="table-wrap"><table class="data-table" id="reportTable"><thead id="reportHead"></thead><tbody id="reportBody"></tbody></table></div>
      <div id="reportMore" style="text-align:center; padding:10px; font-size:11px; color:var(--slate); display:none">… plus more rows. Download Excel for full data.</div>
    </div>
    <div id="reportEmpty" style="text-align:center; padding:30px; color:var(--slate)"><div class="empty-stamp"><?= rep_icon('chart') ?></div><div style="font-weight:700; margin-top:12px; color:var(--ink)">No data loaded</div><div style="font-size:12px">Set filters, then choose Build report.</div></div>
  </div></div>
</div>

<script>
const branches = <?= json_encode($branches) ?>;
function switchTab(t){
  document.querySelectorAll('.tab').forEach(el=>el.classList.toggle('active', el.dataset.tab===t));
  document.getElementById('tab-export').style.display = t==='export' ? '' : 'none';
  document.getElementById('tab-analytics').style.display = t==='analytics' ? '' : 'none';
  document.getElementById('tab-custom').style.display = t==='custom' ? '' : 'none';
}
const typeEl=document.getElementById('reportType');
const show=(id, on)=>{ const el=document.getElementById(id); if(el) el.style.display=on?'':'none'; };
function onTypeChange(){
  const v=typeEl.value;
  const is = (ids)=> ids.includes(v);
  show('wrap_member_type', ['issues','fines'].includes(v));
  show('wrap_book_code', ['issues','fines'].includes(v));
  show('wrap_member_id', ['issues','fines','students','visits'].includes(v));
  show('wrap_start', ['issues','fines','visits','bills'].includes(v));
  show('wrap_end', ['issues','fines','visits','bills'].includes(v));
  show('wrap_branch', ['issues','fines','students','visits'].includes(v));
  show('wrap_year', v==='students');
  show('wrap_has_fines', v==='students');
  show('wrap_category', v==='books');
  show('wrap_availability', v==='books');
  show('wrap_supplier', ['books','bills'].includes(v));
  show('wrap_has_bills', v==='suppliers');
  show('wrap_status', v==='issues');
  show('wrap_status_fines', v==='fines');
  show('wrap_payment', v==='bills');
}
onTypeChange();

function applyPreset(k){
  const fmt=d=>d.toISOString().slice(0,10);
  const now=new Date(); let s='', e=fmt(now);
  if(k==='today') s=e;
  else if(k==='yesterday'){ let d=new Date(now); d.setDate(d.getDate()-1); s=fmt(d); e=s; }
  else if(k==='last7'){ let d=new Date(now); d.setDate(d.getDate()-6); s=fmt(d); }
  else if(k==='last30'){ let d=new Date(now); d.setDate(d.getDate()-29); s=fmt(d); }
  else if(k==='thisWeek'){ let d=new Date(now); const day=d.getDay(); d.setDate(d.getDate()-day+(day===0?-6:1)); s=fmt(d); }
  else if(k==='thisMonth'){ s=fmt(new Date(now.getFullYear(), now.getMonth(),1)); }
  document.getElementById('f_start').value=s;
  document.getElementById('f_end').value=e;
}

const cols = {
  issues: ['Member ID','Member','Type','Branch','Book Title','Code','Issue','Due','Returned','Status'],
  fines: ['Member ID','Member','Type','Branch','Book Title','Amount','Days','Created','Paid','Status'],
  students: ['Enrollment','Name','Branch','Year','Contact','Email','Active','Pending Fines'],
  books: ['Code','Title','Author','ISBN','Category','Total','Avail','Supplier','Location'],
  visits: ['Enrollment','Student','Branch','Year','Check-In','Check-Out','Method','Verification'],
  suppliers: ['ID','Name','Contact','Phone','Email','Address','Bills','Purchases'],
  bills: ['ID','Bill No','Date','Supplier','Payment','Total','Status']
};

let lastData=[];
async function buildReport(){
  const v=typeEl.value;
  const params=new URLSearchParams({type:v});
  const map={ q:'q', member_type:'member_type', book_code:'book_code', member_id:'member_id', start_date:'f_start', end_date:'f_end', branch:'f_branch', year:'f_year', has_fines:'f_has_fines', category:'f_category', availability:'f_availability', supplier_id:'f_supplier', has_bills:'f_has_bills', status:'f_status', payment_mode:'f_payment' };
  // status duality: issues vs fines
  const statusVal = v==='fines' ? document.getElementById('f_status_fines').value : document.getElementById('f_status').value;
  const get=(id)=>document.getElementById(id)?.value||'';
  params.set('type', v);
  if(get('f_q')) params.set('q', get('f_q'));
  if(v==='fines' || v==='issues'){ if(get('f_member_type')!=='all') params.set('member_type', get('f_member_type')); if(get('f_book_code')) params.set('book_code', get('f_book_code')); }
  if(['issues','fines','students','visits'].includes(v) && get('f_member_id')) params.set('member_id', get('f_member_id'));
  if(['issues','fines','visits','bills'].includes(v)){ if(get('f_start')) params.set('start_date', get('f_start')); if(get('f_end')) params.set('end_date', get('f_end')); }
  if(['issues','fines','students','visits'].includes(v) && get('f_branch')) params.set('branch', get('f_branch'));
  if(v==='students'){ if(get('f_year')) params.set('year', get('f_year')); if(get('f_has_fines')!=='all') params.set('has_fines', get('f_has_fines')); }
  if(v==='books'){ if(get('f_category')) params.set('category', get('f_category')); if(get('f_availability')!=='all') params.set('availability', get('f_availability')); if(get('f_supplier')) params.set('supplier_id', get('f_supplier')); }
  if(v==='suppliers' && get('f_has_bills')!=='all') params.set('has_bills', get('f_has_bills'));
  if(v==='issues' && statusVal) params.set('status', statusVal);
  if(v==='fines' && document.getElementById('f_status_fines').value) params.set('status', document.getElementById('f_status_fines').value);
  if(v==='bills'){ if(get('f_supplier')) params.set('supplier_id', get('f_supplier')); if(get('f_payment')) params.set('payment_mode', get('f_payment')); }
  const group=document.getElementById('group_by').value; if(group) params.set('group_by', group);
  const url='/reports/custom?'+params.toString();
  const res=await fetch(url); const data=await res.json();
  lastData=data;
  renderTable(v, data);
}

function renderTable(type, data){
  const head=document.getElementById('reportHead');
  const body=document.getElementById('reportBody');
  const meta=document.getElementById('reportMeta');
  const badge=document.getElementById('reportBadge');
  const more=document.getElementById('reportMore');
  const wrap=document.getElementById('reportResult');
  const empty=document.getElementById('reportEmpty');
  const btnExcel=document.getElementById('btnExcel');
  const btnPdf=document.getElementById('btnPdf');
  const c=cols[type]||[];
  head.innerHTML='<tr>'+c.map(h=>'<th>'+h+'</th>').join('')+'</tr>';
  body.innerHTML='';
  if(!data.length){
    wrap.style.display='none'; empty.style.display=''; btnExcel.style.display='none'; btnPdf.style.display='none';
    empty.innerHTML='<div class="empty-stamp"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></div><div style="font-weight:700; margin-top:12px; color:var(--ink)">No records</div><div style="font-size:12px">Broaden the dates or clear the member filter.</div>';
    return;
  }
  empty.style.display='none'; wrap.style.display=''; btnExcel.style.display=''; btnPdf.style.display='';
  document.getElementById('excelCount').textContent=data.length;
  badge.textContent=data.length+' records'; badge.style.display='';
  meta.textContent='Showing up to 100 rows — download for full dataset';
  const slice=data.slice(0,100);
  const rowsHtml=slice.map(r=>{
    if(type==='issues') return '<tr><td class="mono">'+(r.member_id||'')+'</td><td><b>'+escapeHtml(r.member_name||'')+'</b></td><td><span class="pill pill-slate">'+escapeHtml(r.member_type||'')+'</span></td><td><span class="pill pill-slate">'+escapeHtml(r.branch||'')+'</span></td><td>'+escapeHtml(r.book_title||'')+'</td><td class="mono">'+escapeHtml(r.book_code||'')+'</td><td class="mono">'+escapeHtml(r.issue_date||'')+'</td><td class="mono">'+escapeHtml(r.due_date||'')+'</td><td class="mono">'+escapeHtml(r.return_date||'—')+'</td><td><span class="pill '+(r.status==='returned'?'pill-green':r.status==='overdue'?'pill-red':'pill-amber')+'">'+escapeHtml(r.status||'')+'</span></td></tr>';
    if(type==='fines') return '<tr><td class="mono">'+(r.member_id||'')+'</td><td><b>'+escapeHtml(r.member_name||'')+'</b></td><td><span class="pill pill-slate">'+escapeHtml(r.member_type||'')+'</span></td><td><span class="pill pill-slate">'+escapeHtml(r.branch||'')+'</span></td><td>'+escapeHtml(r.book_title||'')+'</td><td class="mono">₹'+escapeHtml(String(r.amount||0))+'</td><td class="mono">'+escapeHtml(String(r.days_late||''))+'</td><td class="mono">'+escapeHtml(r.created_at||'')+'</td><td class="mono">'+escapeHtml(r.paid_at||'—')+'</td><td><span class="pill '+(r.status==='paid'?'pill-green':'pill-red')+'">'+escapeHtml(r.status||'')+'</span></td></tr>';
    if(type==='students') return '<tr><td class="mono">'+escapeHtml(r.enrollment_no||'')+'</td><td><b>'+escapeHtml(r.name||'')+'</b></td><td><span class="pill pill-slate">'+escapeHtml(r.branch||'')+'</span></td><td>Year '+escapeHtml(String(r.year||''))+'</td><td class="mono">'+escapeHtml(r.contact||'—')+'</td><td>'+escapeHtml(r.email||'—')+'</td><td>'+escapeHtml(String(r.active_issues||0))+'</td><td class="mono" style="color:'+((r.pending_fines>0)?'var(--vermillion)':'')+'">₹'+escapeHtml(String(r.pending_fines||0))+'</td></tr>';
    if(type==='books') return '<tr><td class="mono">'+escapeHtml(r.book_code||'')+'</td><td><b>'+escapeHtml(r.title||'')+'</b></td><td>'+escapeHtml(r.author||'')+'</td><td class="mono">'+escapeHtml(r.isbn||'—')+'</td><td><span class="pill pill-slate">'+escapeHtml(r.category||'')+'</span></td><td class="mono">'+escapeHtml(String(r.quantity_total||0))+'</td><td class="mono" style="color:'+((r.quantity_available>0)?'var(--success)':'var(--vermillion)')+'">'+escapeHtml(String(r.quantity_available||0))+'</td><td>'+escapeHtml(r.supplier_name||'—')+'</td><td>'+escapeHtml(r.shelf_location||'—')+'</td></tr>';
    if(type==='visits') return '<tr><td class="mono">'+escapeHtml(r.enrollment_no||'')+'</td><td><b>'+escapeHtml(r.student_name||'')+'</b></td><td><span class="pill pill-slate">'+escapeHtml(r.branch||'')+'</span></td><td>Year '+escapeHtml(String(r.year||''))+'</td><td class="mono">'+escapeHtml(r.check_in_time||'')+'</td><td class="mono">'+escapeHtml(r.check_out_time||'Active')+'</td><td>'+escapeHtml(r.method||'')+'</td><td><span class="pill '+(r.thumb_verified?'pill-green':'pill-slate')+'">'+(r.thumb_verified?'Fingerprint':'Card')+'</span></td></tr>';
    if(type==='suppliers') return '<tr><td class="mono">'+escapeHtml(String(r.id||''))+'</td><td><b>'+escapeHtml(r.name||'')+'</b></td><td>'+escapeHtml(r.contact_person||'—')+'</td><td class="mono">'+escapeHtml(r.phone||'—')+'</td><td>'+escapeHtml(r.email||'—')+'</td><td>'+escapeHtml(r.address||'—')+'</td><td class="mono">'+escapeHtml(String(r.bills_count||0))+'</td><td class="mono">₹'+escapeHtml(String(r.total_purchases||0))+'</td></tr>';
    if(type==='bills') return '<tr><td class="mono">'+escapeHtml(String(r.id||''))+'</td><td class="mono"><b>'+escapeHtml(r.bill_no||'')+'</b></td><td class="mono">'+escapeHtml(r.purchase_date||'')+'</td><td>'+escapeHtml(r.supplier_name||'')+'</td><td>'+escapeHtml(r.payment_mode||'')+'</td><td class="mono">₹'+escapeHtml(String(r.total_amount||0))+'</td><td><span class="pill '+(r.status==='paid'?'pill-green':'pill-amber')+'">'+escapeHtml(r.status||'')+'</span></td></tr>';
    return '<tr><td colspan="'+c.length+'">'+escapeHtml(JSON.stringify(r))+'</td></tr>';
  }).join('');
  // summary row for some types
  let summary='';
  if(type==='fines' && data.length){ const tot=data.reduce((s,r)=>s+parseFloat(r.amount||0),0); summary='<tr style="font-weight:800; background:var(--paper-2)"><td colspan="5" style="text-align:right">Total</td><td class="mono">₹'+tot.toFixed(2)+'</td><td colspan="4"></td></tr>'; }
  if(type==='students' && data.length){ const tot=data.reduce((s,r)=>s+parseFloat(r.pending_fines||0),0); summary='<tr style="font-weight:800; background:var(--paper-2)"><td colspan="7" style="text-align:right">Total pending fines</td><td class="mono">₹'+tot.toFixed(2)+'</td></tr>'; }
  body.innerHTML=rowsHtml+summary;
  more.style.display = data.length>100 ? '' : 'none';
  meta.textContent='Showing '+(slice.length)+' of '+data.length+' records';
}
function escapeHtml(s){ return String(s||'').replace(/[&<>"]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }

function downloadExcel(){
  if(!lastData.length) return;
  const type=document.getElementById('reportType').value;
  // Build CSV client-side (no xlsx lib needed)
  const headers=cols[type];
  const rows=lastData;
  const csv=[headers.join(',')];
  const esc=v=> '"'+String(v||'').replace(/"/g,'""')+'"';
  // map rows to cols order
  const mappers={
    issues: r=>[r.member_id,r.member_name,r.member_type,r.branch,r.book_title,r.book_code,r.issue_date,r.due_date,r.return_date||'—',r.status],
    fines: r=>[r.member_id,r.member_name,r.member_type,r.branch,r.book_title,r.amount,r.days_late,r.created_at,r.paid_at||'—',r.status],
    students: r=>[r.enrollment_no,r.name,r.branch,r.year,r.contact||'—',r.email||'—',r.active_issues,r.pending_fines],
    books: r=>[r.book_code,r.title,r.author,r.isbn||'—',r.category||'',r.quantity_total,r.quantity_available,r.supplier_name||'—',r.shelf_location||'—'],
    visits: r=>[r.enrollment_no,r.student_name,r.branch,r.year,r.check_in_time,r.check_out_time||'Active',r.method,r.thumb_verified?'Fingerprint':'Card'],
    suppliers: r=>[r.id,r.name,r.contact_person||'—',r.phone||'—',r.email||'—',r.address||'—',r.bills_count,r.total_purchases],
    bills: r=>[r.id,r.bill_no,r.purchase_date,r.supplier_name,r.payment_mode,r.total_amount,r.status]
  };
  const fn=mappers[type];
  rows.forEach(r=> csv.push(fn(r).map(esc).join(',')));
  const blob=new Blob([csv.join('\n')], {type:'text/csv;charset=utf-8;'});
  const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download=type+'_report_'+Date.now()+'.csv'; a.click(); URL.revokeObjectURL(a.href);
}
function downloadPdf(){
  const type=document.getElementById('reportType').value;
  const params=new URLSearchParams({type});
  // include current filters
  const get=id=>document.getElementById(id)?.value||'';
  if(get('f_q')) params.set('q', get('f_q'));
  if(get('f_member_id')) params.set('member_id', get('f_member_id'));
  if(get('f_book_code')) params.set('book_code', get('f_book_code'));
  if(get('f_branch')) params.set('branch', get('f_branch'));
  // ... for demo, redirect to backend pdf (GET)
  window.open('/reports/pdf?'+params.toString(), '_blank');
}

// Barcode wedge for custom builder — fill member_id or book_code
let lastScan=0, buf='';
window.addEventListener('keydown', e=>{
  if(document.getElementById('tab-custom').style.display==='none') return;
  if(e.target.tagName==='INPUT' || e.target.tagName==='TEXTAREA') return;
  const now=Date.now(); if(now-lastScan>30) buf=''; lastScan=now;
  if(e.key==='Enter' && buf){
    const v=buf.trim(); buf='';
    const type=document.getElementById('reportType').value;
    if(['issues','fines'].includes(type)){
      // try member vs book distinction: if student exists fill member else book
      // simple: if looks like enrollment (0902...), member else book
      if(/^\d{4,6}$/.test(v) || /^0+/.test(v)){ document.getElementById('f_book_code').value=v; }
      else if(v.length>4){ document.getElementById('f_member_id').value=v; }
      else document.getElementById('f_q').value=v;
    } else if(['students','visits'].includes(type)){
      document.getElementById('f_member_id').value=v;
    } else if(['books','bills'].includes(type)){
      document.getElementById('f_q').value=v;
    }
    e.preventDefault();
  } else if(e.key.length===1) buf+=e.key;
});
</script>

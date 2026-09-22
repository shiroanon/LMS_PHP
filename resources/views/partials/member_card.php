<?php /* Shared popup record card for borrowers + staff (mirrors original detail modals). Usage: include once per list page, then MemberCard.open('students'| 'staff', id). */ ?>
<style>
.mc-head{display:flex; gap:16px; align-items:center; padding:16px; background:var(--paper-2); border:1px solid var(--border); border-radius:14px; margin-bottom:14px; flex-wrap:wrap}
.mc-avatar{width:64px; height:64px; border-radius:50%; background:var(--maroon); color:#fff; display:grid; place-items:center; font-family:Fraunces,Georgia,serif; font-weight:900; font-size:26px; flex:none; box-shadow:var(--shadow-stamp); border:3px solid var(--paper-3); outline:1px solid var(--border-strong)}
.mc-name{font-family:Fraunces,Georgia,serif; font-weight:900; font-size:21px; letter-spacing:-.02em; line-height:1.1}
.mc-badges{display:flex; gap:6px; flex-wrap:wrap; margin-top:6px}
.mc-meta{font-size:11px; color:var(--slate); margin-top:6px; display:flex; gap:12px; flex-wrap:wrap}
.mc-stats{display:grid; gap:10px; margin-bottom:4px}
.mc-stat{background:var(--paper-3); border:1px solid var(--border); border-radius:12px; padding:10px 12px; text-align:center}
.mc-stat b{display:block; font-family:Fraunces,Georgia,serif; font-weight:900; font-size:20px; line-height:1}
.mc-stat span{font-size:10px; letter-spacing:.1em; text-transform:uppercase; color:var(--slate); font-weight:700}
.mc-kv{display:grid; grid-template-columns:1fr 1fr; gap:0 16px}
@media(max-width:640px){.mc-kv{grid-template-columns:1fr}}
.mc-kv>div{padding:8px 0; border-bottom:1px solid var(--border)}
.mc-kv .k{font-size:10px; letter-spacing:.12em; text-transform:uppercase; color:var(--slate); font-weight:700; margin-bottom:2px}
.mc-kv .v{font-weight:600; font-size:13px}
.mc-sum{display:flex; gap:12px; margin-bottom:12px; flex-wrap:wrap}
.mc-sum>div{padding:10px 16px; border-radius:12px; text-align:center; border:1px solid var(--border)}
.mc-sum b{display:block; font-family:Fraunces,Georgia,serif; font-size:18px}
.mc-sum span{font-size:10px; letter-spacing:.1em; text-transform:uppercase; color:var(--slate); font-weight:700}
</style>
<div id="mcOverlay" class="modal-overlay" style="display:none">
  <div class="modal" onclick="event.stopPropagation()" style="max-width:880px">
    <div class="modal-header"><h2 id="mcTitle">Record</h2><button class="btn btn-ghost btn-icon" onclick="MemberCard.close()" aria-label="Close"><svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg></button></div>
    <div class="modal-body" id="mcBody"></div>
    <div class="modal-footer" id="mcFoot" style="display:none; justify-content:space-between">
      <span class="mono" style="font-size:11px; color:var(--slate)" id="mcFootNote"></span>
      <div style="display:flex; gap:8px"><button class="btn btn-ghost" onclick="MemberCard.close()">Close</button><a class="btn btn-primary btn-small" id="mcFullLink" href="#">Open full record →</a></div>
    </div>
  </div>
</div>
<script>
const MemberCard = (() => {
  let data = null, kind = 'students', tab = 'record';
  const esc = s => String(s ?? '').replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
  const pill = (txt, cls) => '<span class="pill ' + cls + '">' + esc(txt) + '</span>';
  const statusPill = st => st === 'active' || st === 'paid' || st === 'returned' || st === 'fulfilled'
    ? pill(st, 'pill-green')
    : (st === 'passed_out' || st === 'issued' || st === 'pending' || st === 'notified' ? pill(st, 'pill-amber') : pill(st || '—', 'pill-red'));
  const money = n => '₹' + Number(n || 0).toLocaleString('en-IN');
  const overlay = () => document.getElementById('mcOverlay');

  function open(k, id) {
    kind = k; tab = 'record'; data = null;
    document.getElementById('mcTitle').textContent = k === 'staff' ? 'Staff card' : 'Borrower card';
    document.getElementById('mcBody').innerHTML = '<div class="empty"><h3>Fetching record…</h3></div>';
    document.getElementById('mcFoot').style.display = 'none';
    overlay().style.display = 'flex';
    fetch('/' + k + '/' + encodeURIComponent(id) + '/card', {headers: {'Accept': 'application/json'}})
      .then(r => { if (!r.ok) throw 0; return r.json(); })
      .then(d => { data = d; render(); })
      .catch(() => { document.getElementById('mcBody').innerHTML = '<div class="empty"><h3>Could not load record</h3><p>Try again or open the full page.</p></div>'; });
  }
  function close() { overlay().style.display = 'none'; data = null; }
  function go(t) { tab = t; render(); }
  function toggleStaffEdit() {
    const p = document.getElementById('mcStaffEdit');
    if (p) p.style.display = p.style.display === 'none' ? '' : 'none';
  }
  function saveStaff(ev) {
    ev.preventDefault();
    const form = ev.target;
    const btn = form.querySelector('button.btn-primary');
    if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }
    fetch(form.action, {method: 'POST', body: new FormData(form)})
      .then(() => open(kind, data.record.id))
      .catch(() => { if (btn) { btn.disabled = false; btn.textContent = 'Save changes'; } });
    return false;
  }

  function head(r, isStaff) {
    const initial = esc((r.name || '?')[0].toUpperCase());
    const staffActive = (r.status || 'active') === 'active';
    const badges = isStaff
      ? pill(r.loan_category || 'Staff', 'pill-slate') + (r.membership_type ? pill(r.membership_type, 'pill-slate') : '') + (staffActive ? pill('Active', 'pill-green') : pill('Inactive', 'pill-red'))
      : pill(r.branch || '—', 'pill-slate') + pill('Year ' + (r.year ?? '—'), 'pill-slate') + statusPill(r.status);
    const ids = isStaff
      ? '<span class="mono">ID ' + esc(r.library_id || '—') + '</span>' + (r.start_date ? '<span>Since <b class="mono">' + esc(r.start_date) + '</b></span>' : '')
      : '<span class="mono">' + esc(r.library_id || r.enrollment_no || '—') + '</span>'
        + (r.contact ? '<span>TEL <b class="mono">' + esc(r.contact) + '</b></span>' : '')
        + (r.email ? '<span>MAIL <b>' + esc(r.email) + '</b></span>' : '');
    return '<div class="mc-head"><div class="mc-avatar">' + initial + '</div>'
      + '<div style="flex:1; min-width:200px"><div class="mc-name"' + ((!isStaff && !r.library_id) ? ' style="color:var(--vermillion)"' : '') + '>' + esc(r.name) + '</div>'
      + '<div class="mc-badges">' + badges + ((!isStaff && !r.library_id) ? pill('No library ID', 'pill-red') : '') + '</div><div class="mc-meta">' + ids + '</div></div></div>';
  }

  function stats(s, isStaff) {
    const cells = [
      ['<b>' + s.active + '</b><span>On loan</span>', ''],
      ['<b>' + s.total + '</b><span>All issues</span>', ''],
      ['<b style="color:' + (s.pending > 0 ? 'var(--vermillion)' : 'var(--success)') + '">' + money(s.pending) + '</b><span>Pending fines</span>', ''],
    ];
    if (!isStaff) cells.push(['<b style="color:' + (s.overdue > 0 ? 'var(--vermillion)' : 'inherit') + '">' + s.overdue + '</b><span>Overdue</span>', '']);
    return '<div class="mc-stats" style="grid-template-columns:repeat(' + cells.length + ',1fr)">' + cells.map(c => '<div class="mc-stat">' + c[0] + '</div>').join('') + '</div>';
  }

  function tabs(isStaff) {
    const list = isStaff ? [['record','Record'],['loans','Loans'],['fines','Fines']]
      : [['record','Record'],['loans','Loans'],['fines','Fines'],['visits','Visits'],['reservations','Reservations'],['barcode','Barcode']];
    return '<div class="tabs">' + list.map(([id, label]) => '<button class="tab' + (tab === id ? ' active' : '') + '" onclick="MemberCard.go(\'' + id + '\')">' + label + '</button>').join('') + '</div>';
  }

  function kv(label, val, mono) {
    return '<div><div class="k">' + esc(label) + '</div><div class="v' + (mono ? ' mono' : '') + '">' + esc(val || '—') + '</div></div>';
  }

  function recordPane(r, isStaff) {
    let h;
    if (isStaff) {
      const st = r.status || 'active';
      const stPill = st === 'active' ? pill('Active', 'pill-green') : pill('Inactive', 'pill-red');
      h = kv('Staff ID', r.library_id, 1) + kv('Full name', r.name) + kv('Department', r.loan_category)
        + kv('Membership', r.membership_type)
        + '<div><div class="k">Status</div><div class="v">' + stPill + '</div></div>'
        + kv('Start date', r.start_date, 1) + kv('Date added', r.date_added, 1);
      h += '<div style="grid-column:1/-1; display:flex; gap:8px; margin-top:12px; flex-wrap:wrap">'
        + '<button class="btn btn-primary btn-small" onclick="MemberCard.toggleStaffEdit()">Edit details</button>'
        + '<form method="post" action="/staff/' + encodeURIComponent(r.id) + '/delete" onsubmit="return confirm(\'Delete this staff record? Linked loans and fines go with it.\')" style="display:inline; margin:0"><button class="btn btn-ghost btn-small" style="color:var(--vermillion)">Delete</button></form></div>'
        + '<div id="mcStaffEdit" style="display:none; grid-column:1/-1; margin-top:4px; background:var(--paper-2); border:1px solid var(--border); border-radius:12px; padding:12px">'
        + '<form method="post" action="/staff/' + encodeURIComponent(r.id) + '" onsubmit="return MemberCard.saveStaff(event)" style="display:grid; gap:10px">'
        + '<div class="field"><label>Full name *</label><input class="input" name="name" required value="' + esc(r.name) + '"></div>'
        + '<div class="field"><label>Staff ID</label><input class="input mono" name="library_id" value="' + esc(r.library_id) + '"></div>'
        + '<div class="form-row"><div class="field"><label>Department</label><input class="input" name="loan_category" value="' + esc(r.loan_category) + '"></div>'
        + '<div class="field"><label>Membership</label><input class="input" name="membership_type" value="' + esc(r.membership_type) + '"></div></div>'
        + '<div class="field"><label>Status</label><select name="status" class="select"><option value="active"' + (st === 'active' ? ' selected' : '') + '>Active</option><option value="inactive"' + (st !== 'active' ? ' selected' : '') + '>Inactive</option></select></div>'
        + '<div style="display:flex; gap:8px; justify-content:flex-end"><button type="button" class="btn btn-ghost btn-small" onclick="MemberCard.toggleStaffEdit()">Cancel</button><button class="btn btn-primary btn-small">Save changes</button></div>'
        + '</form></div>';
    } else {
      h = kv('Name', r.name) + kv('Library ID', r.library_id || r.enrollment_no, 1) + kv('Enrollment', r.enrollment_no, 1);
      if (r.barcode_data && r.barcode_data !== (r.library_id || r.enrollment_no)) h += kv('Barcode', r.barcode_data, 1);
      h += kv('Branch', r.branch) + kv('Year', (r.year ?? '') + ' yr', 1);
        + kv('Status', r.status) + kv('Login', r.username, 1) + kv('Contact', r.contact, 1) + kv('Email', r.email)
        + kv("Father's name", r.father_name) + kv('Gender', r.gender) + kv('Date of birth', r.dob, 1)
        + kv('Admitted', r.admission_date, 1) + kv('Last gate visit', (r.last_library_visit || '').slice(0, 16), 1);
      if (r.address) h += '<div style="grid-column:1/-1"><div class="k">Address</div><div class="v">' + esc(r.address) + '</div></div>';
      h += '<div style="grid-column:1/-1; display:flex; gap:8px; margin-top:12px; flex-wrap:wrap">'
        + '<form method="post" action="/students/' + encodeURIComponent(r.id) + '/delete" onsubmit="return confirm(\'Remove this borrower? Linked loans, fines, visits and the login go with it.\')" style="display:inline; margin:0"><button class="btn btn-ghost btn-small" style="color:var(--vermillion)">Delete borrower</button></form></div>';
    }
    return '<div class="mc-kv" style="margin-top:12px">' + h + '</div>';
  }

  function loansPane(rows) {
    if (!rows.length) return '<div class="empty"><h3>No loans</h3><p>No issue records on file.</p></div>';
    return '<div class="table-wrap" style="margin-top:12px"><table class="data-table" style="font-size:12px"><thead><tr><th>Book</th><th>Issued</th><th>Due</th><th>Returned</th><th>Status</th></tr></thead><tbody>'
      + rows.map(i => '<tr><td><b>' + esc(i.book_title) + '</b>' + (i.book_isbn ? '<br><span class="mono" style="font-size:11px; color:var(--slate)">ISBN ' + esc(i.book_isbn) + '</span>' : '')
        + '<br><span class="mono" style="font-size:11px; color:var(--slate)">' + esc(i.accession_no || '') + '</span></td>'
        + '<td class="mono">' + esc(i.issue_date) + '</td><td class="mono">' + esc(i.due_date) + '</td><td class="mono">' + esc(i.return_date || '—') + '</td>'
        + '<td>' + statusPill(i.status) + '</td></tr>').join('') + '</tbody></table></div>';
  }

  function finesPane(rows, s) {
    let h = '<div class="mc-sum" style="margin-top:12px"><div style="background:var(--vermillion-bg)"><b style="color:var(--vermillion)">' + money(s.pending) + '</b><span>Pending</span></div>'
      + '<div style="background:var(--success-bg)"><b style="color:var(--success)">' + money(s.paid) + '</b><span>Paid</span></div></div>';
    if (!rows.length) return h + '<div class="empty"><h3>No fines</h3></div>';
    return h + '<div class="table-wrap"><table class="data-table" style="font-size:12px"><thead><tr><th>Book</th><th>Amount</th><th>Reason</th><th>Status</th><th>Date</th></tr></thead><tbody>'
      + rows.map(f => '<tr><td>' + esc(f.book_title) + '</td><td class="mono" style="font-weight:700">' + money(f.amount) + '</td>'
        + '<td>' + esc((f.reason || '').replace(/_/g, ' ')) + '</td><td>' + statusPill(f.status) + '</td><td class="mono">' + esc((f.created_at || '').slice(0, 10)) + '</td></tr>').join('')
      + '</tbody></table></div>';
  }

  function visitsPane(rows) {
    if (!rows.length) return '<div class="empty"><h3>No visits</h3><p>No gate records on file.</p></div>';
    return '<div class="table-wrap" style="margin-top:12px"><table class="data-table" style="font-size:12px"><thead><tr><th>Date</th><th>In</th><th>Out</th><th>Method</th></tr></thead><tbody>'
      + rows.map(v => '<tr><td class="mono">' + esc((v.check_in_time || '').slice(0, 10)) + '</td><td class="mono">' + esc((v.check_in_time || '').slice(11, 16)) + '</td>'
        + '<td class="mono">' + esc(v.check_out_time ? v.check_out_time.slice(11, 16) : 'inside') + '</td><td>' + esc(v.method || 'manual') + '</td></tr>').join('')
      + '</tbody></table></div>';
  }

  function reservationsPane(rows) {
    if (!rows.length) return '<div class="empty"><h3>No reservations</h3></div>';
    return '<div class="table-wrap" style="margin-top:12px"><table class="data-table" style="font-size:12px"><thead><tr><th>Book</th><th>Reserved</th><th>Status</th></tr></thead><tbody>'
      + rows.map(r => '<tr><td><b>' + esc(r.book_title) + '</b></td><td class="mono">' + esc((r.reservation_date || '').slice(0, 10)) + '</td><td>' + statusPill(r.status) + '</td></tr>').join('')
      + '</tbody></table></div>';
  }

  function barcodePane(r) {
    const code = r.barcode_data || r.enrollment_no || '';
    if (!code) return '<div class="empty"><h3>No barcode</h3></div>';
    const url = '/barcode/render?text=' + encodeURIComponent(code);
    return '<div style="text-align:center; padding:18px 0">'
      + '<div class="mc-name" style="font-size:16px; margin-bottom:10px">' + esc(r.name) + '</div>'
      + '<div style="display:inline-block; padding:14px; background:#fff; border:1px solid var(--border); border-radius:12px"><img src="' + url + '" alt="barcode" style="max-width:100%; height:64px"><div class="mono" style="font-size:11px; color:var(--slate); margin-top:4px">' + esc(code) + '</div></div>'
      + '<div><button class="btn btn-primary btn-small" style="margin-top:12px" onclick="MemberCard.printBarcode()">Print barcode</button></div></div>';
  }

  function printBarcode() {
    const r = data.record;
    const code = r.barcode_data || r.enrollment_no || '';
    const w = window.open('', '_blank');
    if (!w) return;
    w.document.write('<!doctype html><html><head><meta charset="utf-8"><title>Barcode — ' + esc(r.name) + '</title>'
      + '<style>body{font-family:system-ui,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}.c{border:1px solid #ccc;border-radius:10px;padding:20px;text-align:center}.n{font-weight:800;margin-bottom:8px}img{height:64px}</style></head><body><div class="c">'
      + '<div class="n">' + esc(r.name) + '</div><img src="/barcode/render?text=' + encodeURIComponent(code) + '"><div>' + esc(code) + '</div>'
      + '<script>onload=function(){focus();print()}<\/script></div></body></html>');
    w.document.close();
  }

  function render() {
    if (!data) return;
    const isStaff = kind === 'staff';
    const r = data.record, s = data.stats;
    let pane = '';
    if (tab === 'record') pane = recordPane(r, isStaff);
    else if (tab === 'loans') pane = loansPane(data.issues || []);
    else if (tab === 'fines') pane = finesPane(data.fines || [], s);
    else if (tab === 'visits') pane = visitsPane(data.visits || []);
    else if (tab === 'reservations') pane = reservationsPane(data.reservations || []);
    else if (tab === 'barcode') pane = barcodePane(r);
    document.getElementById('mcBody').innerHTML = head(r, isStaff) + stats(s, isStaff) + tabs(isStaff) + pane;
    document.getElementById('mcFoot').style.display = 'flex';
    document.getElementById('mcFootNote').textContent = s.total + ' issues · ' + money(s.pending) + ' pending';
    document.getElementById('mcFullLink').href = '/' + kind + '/' + r.id;
  }

  document.addEventListener('keydown', e => { if (e.key === 'Escape' && overlay() && overlay().style.display === 'flex') close(); });
  return {open, close, go, toggleStaffEdit, saveStaff, printBarcode};
})();
</script>

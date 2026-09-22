document.addEventListener('DOMContentLoaded',()=>{
  const btn=document.getElementById('drawerBtn');
  const sb=document.getElementById('sidebar');
  if(btn&&sb) btn.addEventListener('click',()=>sb.classList.toggle('open'));
  // flash auto-dismiss
  setTimeout(()=>{ document.querySelectorAll('[data-flash]').forEach(el=>el.style.display='none'); }, 3500);
  // barcode scanner hook — mirrors server useBarcodeScanner.js: 30ms threshold, Enter fires
  let buf=''; let last=0;
  const handler=(e)=>{
    if(e.target.tagName==='INPUT'||e.target.tagName==='TEXTAREA'||e.target.tagName==='SELECT') return;
    const now=Date.now();
    if(now-last>30) buf='';
    last=now;
    if(e.key==='Enter' && buf){
      // prefer focused [data-barcode-input], else first, else topbar search
      let inp=document.activeElement?.matches('[data-barcode-input]') ? document.activeElement : document.querySelector('[data-barcode-input]:focus') || document.querySelector('input[data-barcode-input]') || document.querySelector('[data-barcode-input]');
      if(!inp) inp=document.querySelector('.search input');
      if(inp){ inp.value=buf; inp.focus(); inp.dispatchEvent(new Event('input',{bubbles:true})); inp.dispatchEvent(new Event('change',{bubbles:true})); }
      buf='';
      e.preventDefault();
    } else if(e.key.length===1) buf+=e.key;
  };
  window.addEventListener('keydown', handler);
});

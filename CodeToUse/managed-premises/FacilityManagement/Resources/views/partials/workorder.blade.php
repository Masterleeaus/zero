@once
<script>
(function(){
  function addBtn(selector, buildUrl, text){
    document.querySelectorAll(selector).forEach(function(a){
      if (a.dataset.woBtn === '1') return;
      var b = document.createElement('button');
      b.className = 'btn btn-sm btn-outline-secondary ms-2';
      b.textContent = text;
      b.type = 'button';
      b.addEventListener('click', async function(){
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const id = a.dataset.id || a.getAttribute('data-id') || (a.href||'').match(/\/(\d+)/)?.[1] || '';
        const url = buildUrl(id);
        try {
          const res = await fetch(url, {method:'POST', headers:{'X-CSRF-TOKEN':token}});
          // If server redirects (JobManagement present), follow it:
          if (res.redirected) window.location = res.url;
          else {
            const j = await res.json();
            alert(j.message || 'Done');
          }
        } catch(e){ alert('Failed to create work order'); }
      });
      a.parentNode.insertBefore(b, a.nextSibling);
      a.dataset.woBtn = '1';
    });
  }
  // Add next to links that look like entity detail links
  addBtn('a[href*="/inspections/"]', function(id){ return '/inspections/'+id+'/create-work-order'; }, 'Create Work Order');
  addBtn('a[href*="/assets/"]', function(id){ return '/assets/'+id+'/create-work-order'; }, 'Create Work Order');
})();
</script>
@endonce

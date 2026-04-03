@once
<script>
(function(){
  async function post(url){
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const r = await fetch(url, {method:'POST', headers:{'X-CSRF-TOKEN':token,'Accept':'application/json'}});
    return r.json();
  }
  function inject(label, urlBuilder, selector){
    document.querySelectorAll(selector).forEach(function(node){
      var b=document.createElement('button');
      b.className='btn btn-sm btn-outline-primary ms-2';
      b.textContent=label;
      b.addEventListener('click', async function(){
        const id = node.dataset.id || node.getAttribute('data-id') || (node.href||'').match(/\/(\d+)/)?.[1] || '';
        if(!id) return alert('No id detected');
        const url = urlBuilder(id);
        try{
          const j=await post(url);
          alert((j.text||'').slice(0,2000));
        }catch(e){ alert('AI call failed'); }
      });
      node.parentNode.insertBefore(b, node.nextSibling);
    });
  }
  // Heuristics for units, assets, docs
  inject('AI Checklist', function(id){ return '/units/'+id+'/ai-checklist'; }, 'a[href*="/units/"][data-ai!="no"]');
  inject('AI PM Plan', function(id){ return '/assets/'+id+'/ai-pm'; }, 'a[href*="/assets/"][data-ai!="no"]');
  inject('AI Doc Summary', function(id){ return '/docs/'+id+'/ai-summary'; }, 'a[href*="/docs/"][data-ai!="no"]');
})();
</script>
@endonce

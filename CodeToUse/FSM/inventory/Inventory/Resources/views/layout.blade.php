@if(session('ok'))<div style="background:#e6ffed;padding:8px;border:1px solid #b7eb8f;margin:8px 0">{{ session('ok') }}</div>@endif
<style>
/* inventory-min */
:root{ --pri:#2563eb; --ok:#16a34a; --warn:#f59e0b; --danger:#dc2626; --bg:#0b1220; --fg:#e5e7eb; --mut:#9ca3af; }
body{ background:#0b1220; color:var(--fg); font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif; }
a{ color:#93c5fd; text-decoration:none }
a:hover{text-decoration:underline}
.btn{ display:inline-block; padding:.45rem .8rem; border-radius:.42rem; border:1px solid transparent; background:#1f2937; color:var(--fg); }
.btn:hover{ filter:brightness(1.1) }
.btn-primary{ background:var(--pri); }
.btn-ok{ background:var(--ok); }
.btn-warn{ background:var(--warn); }
.btn-danger{ background:var(--danger); }
.table{ width:100%; border-collapse:collapse; margin-top:.5rem; }
.table th,.table td{ border:1px solid #1f2937; padding:.5rem .6rem; }
.table th{ background:#111827; text-align:left; }
.flash{ background:#052e16; border:1px solid #14532d; padding:.6rem .8rem; border-radius:.42rem; margin:.75rem 0 }
.controls{ display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; margin:.5rem 0; }
.badge{ background:#111827; border:1px solid #374151; padding:.1rem .45rem; border-radius:.35rem; color:#cbd5e1; font-size:.85rem }
input,select{ background:#0f172a; color:var(--fg); border:1px solid #334155; padding:.4rem .5rem; border-radius:.35rem; }
table[data-enhanced] + script + script, table[data-enhanced] + script { display:none } /* keep page clean */
dialog.modal{ border:none; border-radius:10px; background:#0f172a; color:var(--fg); padding:0; }
.modal header{ padding:.8rem 1rem; border-bottom:1px solid #1f2937; font-weight:600 }
.modal main{ padding:1rem }
.modal footer{ padding:1rem; border-top:1px solid #1f2937; display:flex; gap:.5rem; justify-content:flex-end }
</style>
@if(session('ok'))<div class="flash">{{ session('ok') }}</div>@endif
<header id="inv-topbar" style="position:sticky; top:0; z-index:50; background:#0b1220; border-bottom:1px solid #1f2937; padding:.6rem 1rem; display:flex; gap:.6rem; align-items:center; flex-wrap:wrap">
  <strong>Inventory</strong>
  <a class="btn" href="/inventory">Hub</a>
  <a class="btn" href="/inventory/items">Items</a>
  <a class="btn" href="/inventory/warehouses">Warehouses</a>
  <a class="btn" href="/inventory/movements">Movements</a>
  <a class="btn" href="/inventory/stocktakes">Stocktakes</a>
  <a class="btn" href="/inventory/audit">Audit</a>
</header>
<div style="padding:16px">@yield('content')</div>

<script>
// Confirm helper: attach to any .js-confirm button
(function(){
  function wire(){
    document.querySelectorAll('[data-confirm]').forEach(function(btn){
      if (btn._wired) return; btn._wired = true;
      btn.addEventListener('click', function(ev){
        var msg = btn.getAttribute('data-confirm') || 'Are you sure?';
        var form = btn.closest('form');
        if(!form){ if(!confirm(msg)) ev.preventDefault(); return; }
        if(!window.confirm(msg)){ ev.preventDefault(); }
      });
    });
  }
  document.addEventListener('DOMContentLoaded', wire);
})();
</script>

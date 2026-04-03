@extends('inventory::layout')
@section('content')
<h2>Stock Movements</h2>
<p><a class="btn" href="{{ route('inventory.moves.trashed') }}">Trashed</a></p>
<div class="controls">
<label class="badge"><input type="checkbox" id="chk-all"> Select All</label>
<form method="POST" action="{{ route('inventory.moves.bulkDelete') }}" style="display:inline"> @csrf <button class="btn btn-danger" data-confirm="Move selected to trash?">Bulk Delete</button></form>

</div>
<script>document.addEventListener('DOMContentLoaded',function(){var a=document.getElementById('chk-all');if(!a)return;a.addEventListener('change',function(){document.querySelectorAll('input[name="ids[]"]').forEach(function(cb){cb.checked=a.checked;});});document.querySelectorAll('form[action*="bulk-"]').forEach(function(f){f.addEventListener('submit',function(ev){var sel=Array.from(document.querySelectorAll('input[name="ids[]"]:checked')).map(el=>el.value);if(sel.length===0){ev.preventDefault();alert('Select at least one row.');return;}sel.forEach(function(id){var i=document.createElement('input');i.type='hidden';i.name='ids[]';i.value=id;f.appendChild(i);});});});});</script>
<a class="btn" href="{{ route('inventory.moves.create') }}">+ Record Movement</a>
<table data-enhanced border="1" cellspacing="0" cellpadding="6">
<tr><th><input type="checkbox" id="chk-all"></th><th>ID</th><th>Item</th><th>Warehouse</th><th>Type</th><th>Qty Change</th><th>Note</th><th>At</th></tr>
@foreach($moves as $m)
<tr><td><input type="checkbox" name="ids[]" value="{{ $m->id }}"></td>
<td>{{ $m->id }}</td>
<td>{{ optional($m->item)->name }} (#{{ $m->item_id }})</td>
<td>{{ optional($m->warehouse)->name }}</td>
<td>{{ $m->type }}</td>
<td>{{ $m->qty_change }}</td>
<td>{{ $m->note }}</td>
<td>{{ $m->created_at }}</td>
</tr>
@endforeach
</table>
{{ $moves->links() }}
@endsection
\n<script>
(function(){
  function makeTableInteractive(table){
    if(!table) return;
    // Add search input
    var input = document.createElement('input');
    input.placeholder = 'Search...';
    input.style.margin = '8px 0';
    table.parentNode.insertBefore(input, table);

    input.addEventListener('input', function(){
      var q = this.value.toLowerCase();
      var rows = table.tBodies[0].rows;
      for (var i=0;i<rows.length;i++){
        var show=false;
        for (var j=0;j<rows[i].cells.length;j++){
          if (rows[i].cells[j].innerText.toLowerCase().indexOf(q)>=0){ show=true; break; }
        }
        rows[i].style.display = show ? '' : 'none';
      }
    });

    // Sort on header click
    var headers = table.tHead ? table.tHead.rows[0].cells : [];
    for (let k=0;k<headers.length;k++){
      headers[k].style.cursor = 'pointer';
      headers[k].addEventListener('click', function(){
        var asc = this.getAttribute('data-asc') !== 'true'; // toggle
        this.setAttribute('data-asc', asc ? 'true' : 'false');
        var rows = Array.from(table.tBodies[0].rows);
        rows.sort(function(a,b){
          var va = a.cells[k].innerText.trim().toLowerCase();
          var vb = b.cells[k].innerText.trim().toLowerCase();
          if (!isNaN(parseFloat(va)) && !isNaN(parseFloat(vb))){
            va = parseFloat(va); vb = parseFloat(vb);
            return asc ? (va - vb) : (vb - va);
          }
          return asc ? va.localeCompare(vb) : vb.localeCompare(va);
        });
        rows.forEach(r => table.tBodies[0].appendChild(r));
      });
    }
  }
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('table[data-enhanced]').forEach(makeTableInteractive);
  });
})();
</script>\n
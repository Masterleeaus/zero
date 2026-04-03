@extends('inventory::layout')
@section('content')
<h2>Items</h2>

<div class="controls">
  <label class="badge"><input type="checkbox" id="chk-all"> Select All</label>
  <form method="POST" action="{{ route('inventory.items.bulkDelete') }}" style="display:inline"> @csrf <button class="btn btn-danger" data-confirm="Move selected to trash?">Bulk Delete</button></form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  var all = document.getElementById('chk-all');
  if(!all) return;
  all.addEventListener('change', function(){
    document.querySelectorAll('input[name="ids[]"]').forEach(function(cb){ cb.checked = all.checked; });
  });
  document.querySelectorAll('form[action*="bulk-"]').forEach(function(f){
    f.addEventListener('submit', function(ev){
      var sel = Array.from(document.querySelectorAll('input[name="ids[]"]:checked')).map(el=>el.value);
      if(sel.length===0){ ev.preventDefault(); alert('Select at least one row.'); return; }
      sel.forEach(function(id){ var i = document.createElement('input'); i.type='hidden'; i.name='ids[]'; i.value=id; f.appendChild(i); });
    });
  });
});
</script>

<p>
<a class="btn" href="{{ route('inventory.items.export') }}">Export CSV</a>
<form method="POST" action="{{ route('inventory.items.import') }}" enctype="multipart/form-data" style="display:inline-block;margin-left:12px">
@csrf <input type="file" name="csv" accept=".csv"> <button class="btn btn-ok">Import CSV</button>
</form>
</p>

<a class="btn" href="{{ route('inventory.items.create') }}" class="btn btn-primary">+ New Item</a>
<table data-enhanced border="1" cellspacing="0" cellpadding="6">
<tr><th><input type="checkbox" id="chk-all"></th><th>ID</th><th>Name</th><th>SKU</th><th>Qty</th><th>Category</th><th>Unit Price</th><th></th></tr>
@foreach($items as $it)
<tr><td><input type="checkbox" name="ids[]" value="{{ $it->id }}"></td>
<td>{{ $it->id }}</td>
<td>{{ $it->name }}</td>
<td>{{ $it->sku }}</td>
<td>{{ $it->qty }}</td>
<td>{{ $it->category }}</td>
<td>{{ number_format($it->unit_price,2) }}</td>
<td>
  <a class="btn" href="{{ route('inventory.items.edit',$it) }}">Edit</a>
  <form action="{{ route('inventory.items.destroy',$it) }}" method="POST" style="display:inline">@csrf @method('DELETE') <button data-confirm="Delete this record?">Delete</button></form>
</td>
</tr>
@endforeach
</table>
{{ $items->links() }}
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
</script>\n<p><a class="btn" href="{{ route('inventory.items.trashed') }}">Trashed</a></p>
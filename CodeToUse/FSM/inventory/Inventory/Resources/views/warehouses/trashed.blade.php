@extends('inventory::layout')
@section('content')
<h2>Warehouses (Trashed)</h2>

<div class="controls">
  <label class="badge"><input type="checkbox" id="chk-all"> Select All</label>
  <form method="POST" action="{{ route('inventory.wh.bulkRestore') }}" style="display:inline"> @csrf <button class="btn btn-ok">Bulk Restore</button></form>
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

<table border="1" cellspacing="0" cellpadding="6" data-enhanced>
<tr><th><input type="checkbox" id="chk-all"></th><th>ID</th><th>Name</th><th>Deleted At</th><th></th></tr>
@foreach($rows as $w)
<tr><td><input type="checkbox" name="ids[]" value="{{ $w->id }}"></td>
<td>{{ $w->id }}</td>
<td>{{ $w->name }}</td>
<td>{{ $w->deleted_at }}</td>
<td>
<form method="POST" action="{{ route('inventory.wh.restore',$w->id) }}">@csrf <button class="btn btn-ok">Restore</button></form>
</td>
</tr>
@endforeach
</table>
{{ $rows->links() }}
@endsection

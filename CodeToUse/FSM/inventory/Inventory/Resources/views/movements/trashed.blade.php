@extends('inventory::layout')
@section('content')
<h2>Stock Movements (Trashed)</h2>
<div class="controls">
  <label class="badge"><input type="checkbox" id="chk-all"> Select All</label>
  <form method="POST" action="{{ route('inventory.moves.bulkRestore') }}" style="display:inline">@csrf <button class="btn btn-ok">Bulk Restore</button></form>
</div>
<table class="table" data-enhanced>
  <thead><tr><th><input type="checkbox" id="chk-all"></th><th>ID</th><th>Type</th><th>Qty</th><th>Item</th><th>Warehouse</th><th>Deleted At</th><th></th></tr></thead>
  <tbody>
  @foreach($moves as $m)
    <tr>
      <td><input type="checkbox" name="ids[]" value="{{ $m->id }}"></td>
      <td>{{ $m->id }}</td>
      <td>{{ $m->type }}</td>
      <td>{{ $m->qty_change }}</td>
      <td>#{{ $m->item_id }}</td>
      <td>#{{ $m->warehouse_id }}</td>
      <td>{{ $m->deleted_at }}</td>
      <td>
        <form method="POST" action="{{ route('inventory.moves.restore',$m->id) }}">@csrf <button class="btn btn-ok" data-confirm="Restore this movement?">Restore</button></form>
      </td>
    </tr>
  @endforeach
  </tbody>
</table>
{{ $moves->links() }}
<script>
document.addEventListener('DOMContentLoaded',function(){var a=document.getElementById('chk-all');if(!a)return;a.addEventListener('change',function(){document.querySelectorAll('input[name="ids[]"]').forEach(function(cb){cb.checked=a.checked;});});});
</script>
@endsection

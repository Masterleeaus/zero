@extends('inventory::layout')
@section('content')
<h2>Stocktakes (Trashed)</h2>
<div class="controls">
  <label class="badge"><input type="checkbox" id="chk-all"> Select All</label>
  <form method="POST" action="{{ route('inventory.st.bulkRestore') }}" style="display:inline">@csrf <button class="btn btn-ok">Bulk Restore</button></form>
</div>
<table class="table" data-enhanced>
  <thead><tr><th><input type="checkbox" id="chk-all"></th><th>ID</th><th>Ref</th><th>Status</th><th>Deleted At</th><th></th></tr></thead>
  <tbody>
  @foreach($rows as $st)
    <tr>
      <td><input type="checkbox" name="ids[]" value="{{ $st->id }}"></td>
      <td>{{ $st->id }}</td>
      <td>{{ $st->ref }}</td>
      <td>{{ $st->status }}</td>
      <td>{{ $st->deleted_at }}</td>
      <td>
        <form method="POST" action="{{ route('inventory.st.restore',$st->id) }}">@csrf <button class="btn btn-ok" data-confirm="Restore this stocktake?">Restore</button></form>
      </td>
    </tr>
  @endforeach
  </tbody>
</table>
{{ $rows->links() }}
<script>
document.addEventListener('DOMContentLoaded',function(){var a=document.getElementById('chk-all');if(!a)return;a.addEventListener('change',function(){document.querySelectorAll('input[name="ids[]"]').forEach(function(cb){cb.checked=a.checked;});});});
</script>
@endsection

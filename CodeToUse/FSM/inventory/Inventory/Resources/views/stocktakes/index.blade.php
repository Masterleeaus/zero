@extends('inventory::layout')
@section('content')
<h2>Stocktakes</h2>
<p><a class="btn" href="{{ route('inventory.st.trashed') }}">Trashed</a></p>
<div class="controls">
<label class="badge"><input type="checkbox" id="chk-all"> Select All</label>
<form method="POST" action="{{ route('inventory.st.bulkDelete') }}" style="display:inline"> @csrf <button class="btn btn-danger" data-confirm="Move selected to trash?">Bulk Delete</button></form>

</div>
<script>document.addEventListener('DOMContentLoaded',function(){var a=document.getElementById('chk-all');if(!a)return;a.addEventListener('change',function(){document.querySelectorAll('input[name="ids[]"]').forEach(function(cb){cb.checked=a.checked;});});document.querySelectorAll('form[action*="bulk-"]').forEach(function(f){f.addEventListener('submit',function(ev){var sel=Array.from(document.querySelectorAll('input[name="ids[]"]:checked')).map(el=>el.value);if(sel.length===0){ev.preventDefault();alert('Select at least one row.');return;}sel.forEach(function(id){var i=document.createElement('input');i.type='hidden';i.name='ids[]';i.value=id;f.appendChild(i);});});});});</script>
<a href="{{ route('inventory.st.create') }}" class="btn btn-primary">+ New Stocktake</a>
<table border="1" cellspacing="0" cellpadding="6">
<tr><th><input type="checkbox" id="chk-all"></th><th>ID</th><th>Ref</th><th>Warehouse</th><th>Status</th><th></th></tr>
@foreach($rows as $st)
<tr><td><input type="checkbox" name="ids[]" value="{{ $st->id }}"></td>
<td>{{ $st->id }}</td><td>{{ $st->ref }}</td><td>{{ optional($st->warehouse)->name }}</td><td>{{ $st->status }}</td>
<td><a href="{{ route('inventory.st.edit',$st) }}">Open</a></td>
</tr>
@endforeach
</table>
{{ $rows->links() }}
@endsection

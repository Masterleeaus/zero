@extends('inventory::layout')
@section('content')
<h2>New Stocktake</h2>
<form method="POST" action="{{ route('inventory.st.store') }}">
@csrf
<label>Ref <input name="ref"></label><br>
<label>Warehouse
<select name="warehouse_id"><option value="">(none)</option>
@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
</select></label><br>
<button>Create</button>
</form>
@endsection

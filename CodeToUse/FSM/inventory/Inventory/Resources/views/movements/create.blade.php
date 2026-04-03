@extends('inventory::layout')
@section('content')
<h2>Record Movement</h2>
<form method="POST" action="{{ route('inventory.moves.store') }}">
@csrf
<label>Item
<select name="item_id">
@foreach($items as $it)<option value="{{ $it->id }}">{{ $it->name }}</option>@endforeach
</select></label><br>
<label>Warehouse
<select name="warehouse_id">
<option value="">(none)</option>
@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
</select></label><br>
<label>Type
<select name="type">
<option value="in">In</option>
<option value="out">Out</option>
<option value="adjust">Adjust</option>
</select></label><br>
<label>Qty Change <input type="number" name="qty_change" value="1"></label><br>
<label>Note <input name="note"></label><br>
<button>Save</button>
</form>
@endsection

@extends('inventory::layout')
@section('content')
<h2>New Item</h2>
<form method="POST" action="{{ route('inventory.items.store') }}">
@csrf
<label>Name <input name="name" required></label><br>
<label>SKU <input name="sku"></label><br>
<label>Qty <input name="qty" type="number" value="0"></label><br>
<label>Category <input name="category"></label><br>
<label>Unit Price <input name="unit_price" type="number" step="0.01"></label><br>
<button>Save</button>
</form>
@endsection

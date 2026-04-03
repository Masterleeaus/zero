@extends('inventory::layout')
@section('content')
<h2>Edit Item #{{ $item->id }}</h2>
<form method="POST" action="{{ route('inventory.items.update',$item) }}">
@csrf @method('PUT')
<label>Name <input name="name" value="{{ $item->name }}" required></label><br>
<label>SKU <input name="sku" value="{{ $item->sku }}"></label><br>
<label>Qty <input name="qty" type="number" value="{{ $item->qty }}"></label><br>
<label>Category <input name="category" value="{{ $item->category }}"></label><br>
<label>Unit Price <input name="unit_price" type="number" step="0.01" value="{{ $item->unit_price }}"></label><br>
<button>Update</button>
</form>
@endsection

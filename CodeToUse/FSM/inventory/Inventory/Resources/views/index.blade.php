@extends('inventory::layout')
@section('content')
<h1>Inventory Hub</h1>
<ul>
<li><a href="{{ route('inventory.items.index') }}">Items</a></li>
<li><a href="{{ route('inventory.wh.index') }}">Warehouses</a></li>
<li><a href="{{ route('inventory.moves.index') }}">Stock Movements</a></li>
<li><a href="{{ route('inventory.audit.index') }}">Audit Log</a></li>
</ul>
@endsection

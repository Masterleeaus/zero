@extends('inventory::layout')
@section('content')
<h2>Edit Warehouse #{{ $warehouse->id }}</h2>
<form method="POST" action="{{ route('inventory.wh.update',$warehouse) }}">
@csrf @method('PUT')
<label>Name <input name="name" value="{{ $warehouse->name }}" required></label><br>
<label>Code <input name="code" value="{{ $warehouse->code }}"></label><br>
<label>Location <input name="location" value="{{ $warehouse->location }}"></label><br>
<button>Update</button>
</form>
@endsection

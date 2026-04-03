@extends('inventory::layout')
@section('content')
<h2>New Warehouse</h2>
<form method="POST" action="{{ route('inventory.wh.store') }}">
@csrf
<label>Name <input name="name" required></label><br>
<label>Code <input name="code"></label><br>
<label>Location <input name="location"></label><br>
<button>Save</button>
</form>
@endsection

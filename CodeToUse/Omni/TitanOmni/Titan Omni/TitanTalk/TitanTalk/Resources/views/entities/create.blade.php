@extends('titantalk::layouts.base')
@section('title','Create Entity')
@section('body')
<form method="POST" action="{{ route('titantalk.entities.store') }}">@csrf
  <div class="mb-3"><label>Name</label><input name="name" class="form-control" required></div>
  <div class="mb-3"><label>Values (comma separated)</label><input name="values" class="form-control"></div>
  <button class="btn btn-primary">Save</button>
</form>
@endsection

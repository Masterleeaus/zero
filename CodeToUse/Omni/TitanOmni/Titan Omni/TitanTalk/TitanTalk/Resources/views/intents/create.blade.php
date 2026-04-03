@extends('titantalk::layouts.base')
@section('title','Create Intent')
@section('body')
<form method="POST" action="{{ route('titantalk.intents.store') }}">@csrf
  <div class="mb-3"><label>Name</label><input name="name" class="form-control" required></div>
  <div class="mb-3"><label>Description</label><textarea name="description" class="form-control"></textarea></div>
  <button class="btn btn-primary">Save</button>
</form>
@endsection

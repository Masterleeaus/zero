@extends('titantalk::layouts.base')
@section('title','Edit Intent')
@section('body')
<form method="POST" action="{{ route('titantalk.intents.update',$intent->id) }}">@csrf @method('PUT')
  <div class="mb-3"><label>Name</label><input name="name" class="form-control" value="{{ $intent->name }}" required></div>
  <div class="mb-3"><label>Description</label><textarea name="description" class="form-control">{{ $intent->description }}</textarea></div>
  <button class="btn btn-primary">Save</button>
</form>
@endsection

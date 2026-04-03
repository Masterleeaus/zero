@extends('titantalk::layouts.base')
@section('title','Edit Entity')
@section('body')
<form method="POST" action="{{ route('titantalk.entities.update',$entity->id) }}">@csrf @method('PUT')
  <div class="mb-3"><label>Name</label><input name="name" class="form-control" value="{{ $entity->name }}" required></div>
  <div class="mb-3"><label>Values (comma separated)</label><input name="values" class="form-control" value="{{ is_array($entity->values)? implode(', ',$entity->values):'' }}"></div>
  <button class="btn btn-primary">Save</button>
</form>
@endsection

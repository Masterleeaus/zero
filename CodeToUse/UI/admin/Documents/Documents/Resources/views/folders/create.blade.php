@extends('layouts.app')

@section('content')
<div class="container">
  <h1>{{ __('New Folder') }}</h1>

  <form method="POST" action="{{ route('documents.folders.store') }}">
    @csrf
    <input type="hidden" name="parent_id" value="{{ $parentId }}">

    <div class="mb-3">
      <label class="form-label">{{ __('Folder Name') }}</label>
      <input type="text" name="name" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">{{ __('Description') }}</label>
      <textarea name="description" class="form-control" rows="3"></textarea>
    </div>

    <button type="submit" class="btn btn-primary">
      {{ __('Create Folder') }}
    </button>
  </form>
</div>
@endsection

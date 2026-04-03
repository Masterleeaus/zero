@extends('layouts.app')

@section('content')
<div class="container">
  <h1>{{ __('Edit Folder') }}</h1>

  <form method="POST" action="{{ route('documents.folders.update', $folder) }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label class="form-label">{{ __('Folder Name') }}</label>
      <input type="text" name="name" class="form-control" value="{{ $folder->name }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">{{ __('Description') }}</label>
      <textarea name="description" class="form-control" rows="3">{{ $folder->description }}</textarea>
    </div>

    <button type="submit" class="btn btn-primary">
      {{ __('Save') }}
    </button>
  </form>
</div>
@endsection

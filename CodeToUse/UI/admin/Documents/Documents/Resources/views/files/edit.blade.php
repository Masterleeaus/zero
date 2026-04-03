@extends('layouts.app')

@section('content')
<div class="container">
  <h1>{{ __('Edit File') }}</h1>

  <form method="POST" action="{{ route('documents.files.update', $file) }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label class="form-label">{{ __('Display Name') }}</label>
      <input type="text" name="name" class="form-control"
             value="{{ $file->name }}" required>
    </div>

    <div class="form-check mb-3">
      <input class="form-check-input"
             type="checkbox"
             name="is_public"
             value="1"
             id="is_public"
             {{ $file->is_public ? 'checked' : '' }}>
      <label class="form-check-label" for="is_public">
        {{ __('Public link allowed') }}
      </label>
    </div>

    <button type="submit" class="btn btn-primary">
      {{ __('Save') }}
    </button>
  </form>
</div>
@endsection

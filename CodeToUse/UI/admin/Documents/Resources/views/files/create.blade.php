@extends('layouts.app')

@section('content')
<div class="container">
  <h1>{{ __('Upload File') }}</h1>

  <form method="POST"
        action="{{ route('documents.files.store') }}"
        enctype="multipart/form-data">
    @csrf

    <input type="hidden" name="folder_id" value="{{ $folderId }}">

    <div class="mb-3">
      <label class="form-label">{{ __('File') }}</label>
      <input type="file" name="file" class="form-control" required>
    </div>

    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="is_public" value="1" id="is_public">
      <label class="form-check-label" for="is_public">
        {{ __('Public link allowed') }}
      </label>
    </div>

    <button type="submit" class="btn btn-primary">
      {{ __('Upload') }}
    </button>
  </form>
</div>
@endsection

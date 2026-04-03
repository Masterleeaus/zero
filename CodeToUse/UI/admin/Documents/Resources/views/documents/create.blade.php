@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0">{{ __('Create Document') }}</h1>
    <a class="btn btn-outline-secondary" href="{{ route('documents.general') }}">{{ __('Back') }}</a>
  </div>

  <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
    @csrf

    @include('documents::documents.partials.toolbar', [
      'intent' => old('type', 'general') === 'swms' ? 'check_compliance' : 'summarise_standard',
      'record_type' => 'document',
      'record_id' => null,
      'fields' => [
        'title' => old('title'),
        'type' => old('type', 'general'),
        'category' => old('category'),
        'subcategory' => old('subcategory'),
        'body_markdown' => old('body_markdown'),
      ],
    ])

    <div class="mb-3">
      <label class="form-label">{{ __('Template Variables') }}</label>
      <textarea class="form-control" name="vars" rows="4" placeholder="{{ __('One per line: key=value') }}">{{ old('vars') }}</textarea>
      <small class="text-muted">{{ __('Example: job.site=123 Main St') }}</small>
    </div>

    @include('documents::documents.partials.form_fields')

    @include('documents::documents.partials.attachments')

    <button type="submit" class="btn btn-success">{{ __('Save') }}</button>
  </form>
</div>
@endsection

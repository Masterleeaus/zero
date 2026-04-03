@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0">{{ __('Version') }} #{{ $version->version_no }}</h1>
    <a class="btn btn-outline-secondary" href="{{ route('documents.versions.index', $document) }}">{{ __('Back') }}</a>
  </div>

  <div class="card">
    <div class="card-header">
      <strong>{{ $document->title }}</strong>
      <span class="text-muted">— {{ $version->created_at ? $version->created_at->format('Y-m-d H:i') : '' }}</span>
    </div>
    <div class="card-body">
      <pre style="white-space: pre-wrap" class="mb-0">{{ data_get($version->snapshot, 'body_markdown', '') }}</pre>
    </div>
  </div>
</div>
@endsection

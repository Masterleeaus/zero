@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="mb-1">{{ $document->title }}</h1>
      @include('documents::documents.partials.status_badge', ['status' => $document->status])
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-dark" href="{{ route('documents.edit', $document) }}">{{ __('Edit') }}</a>
      <a class="btn btn-outline-secondary" href="{{ route($document->type === 'swms' ? 'documents.swms' : 'documents.general') }}">{{ __('Back') }}</a>
    </div>
  </div>

  <div class="mb-3">
    @include('documents::documents.partials.toolbar', [
      'intent' => $document->type === 'swms' ? 'check_compliance' : 'summarise_standard',
      'record_type' => 'document',
      'record_id' => $document->id,
      'fields' => [
        'title' => $document->title,
        'type' => $document->type,
        'category' => $document->category,
        'subcategory' => $document->subcategory,
        'body_markdown' => $document->body_markdown,
      ],
    ])
  </div>

  <div class="card mb-3">
    <div class="card-header"><strong>{{ __('Document') }}</strong></div>
    <div class="card-body">
      <div class="row mb-2">
        <div class="col-md-3 text-muted">{{ __('Category') }}</div>
        <div class="col-md-9">{{ $document->category ?: '—' }}</div>
      </div>
      <div class="row mb-2">
        <div class="col-md-3 text-muted">{{ __('Subcategory') }}</div>
        <div class="col-md-9">{{ $document->subcategory ?: '—' }}</div>
      </div>
      <div class="row mb-2">
        <div class="col-md-3 text-muted">{{ __('Trade / Role') }}</div>
        <div class="col-md-9">{{ trim(($document->trade ?? '').' '.($document->role ?? '')) ?: '—' }}</div>
      </div>
      <hr>
      <pre style="white-space: pre-wrap;" class="mb-0">{{ $document->body_markdown }}</pre>
    </div>
  </div>

  @include('documents::documents.partials.sections_editor', ['document' => $document])

  @include('documents::documents.partials.attachments', ['document' => $document])
</div>
@endsection

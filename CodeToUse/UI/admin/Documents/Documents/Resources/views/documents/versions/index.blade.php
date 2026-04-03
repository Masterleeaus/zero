@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0">{{ __('Versions') }}: {{ $document->title }}</h1>
    <a class="btn btn-outline-secondary" href="{{ route('documents.edit', $document) }}">{{ __('Back') }}</a>
  </div>

  <div class="card">
    <div class="card-body">
      @if($versions->count())
        <ul class="list-group">
          @foreach($versions as $v)
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <strong>#{{ $v->version_no }}</strong>
                <div class="text-muted small">{{ $v->created_at->format('Y-m-d H:i') }}</div>
              </div>
              <a class="btn btn-sm btn-outline-dark" href="{{ route('documents.versions.show', [$document, $v]) }}">{{ __('View') }}</a>
            </li>
          @endforeach
        </ul>
        <div class="mt-3">{{ $versions->links() }}</div>
      @else
        <p class="text-muted">{{ __('No versions yet (created in later passes).') }}</p>
      @endif
    </div>
  </div>
</div>
@endsection

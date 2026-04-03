@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="mb-1">{{ $document->title }}</h1>
      @include('documents::documents.partials.status_badge', ['status' => $document->status])
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-dark" href="{{ route('documents.show', $document) }}">{{ __('View') }}</a>
      <form method="POST" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm('{{ __('Delete this document?') }}')">
        @csrf
        @method('DELETE')
        <button class="btn btn-outline-danger" type="submit">{{ __('Delete') }}</button>
      </form>
    </div>
  </div>

  <form method="POST" action="{{ route('documents.update', $document) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

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

    @include('documents::documents.partials.form_fields', ['document' => $document])

    @include('documents::documents.partials.attachments', ['document' => $document])

    <button type="submit" class="btn btn-success">{{ __('Save Changes') }}</button>
  </form>

  @include('documents::documents.partials.sections_editor', ['document' => $document])

  <div class="card mb-3">
    <div class="card-header"><strong>{{ __('Share links') }}</strong> <span class="text-muted">{{ __('(scaffold)') }}</span></div>
    <div class="card-body">
      <form method="POST" action="{{ route('documents.share-links.store', $document) }}" class="row g-2">
        @csrf
        <div class="col-md-4">
          <input type="date" class="form-control" name="expires_at" value="{{ old('expires_at') }}" placeholder="{{ __('Expires') }}">
        </div>
        <div class="col-md-6">
          <input class="form-control" name="note" value="{{ old('note') }}" placeholder="{{ __('Note') }}">
        </div>
        <div class="col-md-2">
          <button class="btn btn-primary w-100" type="submit">{{ __('Create') }}</button>
        </div>
      </form>

      @if($document->shareLinks && $document->shareLinks->count())
        <hr>
        <ul class="list-group">
          @foreach($document->shareLinks as $link)
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <div class="small"><strong>{{ $link->token }}</strong></div>
                @if($link->expires_at)<div class="text-muted small">{{ __('Expires:') }} {{ $link->expires_at->format('Y-m-d') }}</div>@endif
                @if($link->note)<div class="text-muted small">{{ $link->note }}</div>@endif
              </div>
              <form method="POST" action="{{ route('documents.share-links.destroy', [$document, $link]) }}">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" type="submit">{{ __('Remove') }}</button>
              </form>
            </li>
          @endforeach
        </ul>
      @endif
    </div>
  </div>

</div>
@endsection

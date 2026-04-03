@php
  $allTags = \Modules\Documents\Entities\DocumentTag::query()->orderBy('name')->get();
@endphp

<div class="card mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h6 class="mb-0">{{ __('Tags') }}</h6>
      <a class="small" href="{{ route('documents.tags.index') }}">{{ __('Manage tags') }}</a>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-2">
      @foreach($document->tags as $tag)
        <span class="badge" style="background: {{ $tag->bg_color ?? '#e5e7eb' }}; color: {{ $tag->text_color ?? '#111827' }};">
          {{ $tag->name }}
          @can('documents.update')
            <form method="POST" action="{{ route('documents.documents.tags.destroy', [$document->id, $tag->id]) }}" class="d-inline">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-link btn-sm p-0 ms-1" style="color: inherit; text-decoration: none;">×</button>
            </form>
          @endcan
        </span>
      @endforeach
      @if($document->tags->isEmpty())
        <span class="text-muted small">{{ __('No tags yet.') }}</span>
      @endif
    </div>

    @can('documents.update')
    <form method="POST" action="{{ route('documents.documents.tags.store', $document) }}" class="d-flex gap-2">
      @csrf
      <select name="tag_id" class="form-select" required>
        <option value="">{{ __('Add tag...') }}</option>
        @foreach($allTags as $t)
          <option value="{{ $t->id }}">{{ $t->name }}</option>
        @endforeach
      </select>
      <button class="btn btn-outline-primary">{{ __('Add') }}</button>
    </form>
    @endcan
  </div>
</div>

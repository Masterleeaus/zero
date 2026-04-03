@if(isset($document))
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>{{ __('Sections') }}</strong>
      <a class="btn btn-sm btn-outline-dark" href="{{ route('documents.versions.index', $document) }}">{{ __('Versions') }}</a>
    </div>
    <div class="card-body">
      @if($document->sections && $document->sections->count())
        <div class="list-group mb-3">
          @foreach($document->sections as $section)
            <div class="list-group-item">
              <div class="d-flex justify-content-between align-items-center">
                <strong>{{ $section->label }}</strong>
                <form method="POST" action="{{ route('documents.sections.destroy', [$document, $section]) }}">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger" type="submit">{{ __('Remove') }}</button>
                </form>
              </div>
              @if($section->content)
                <div class="mt-2 small text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($section->content), 220) }}</div>
              @endif
            </div>
          @endforeach
        </div>
      @else
        <p class="text-muted">{{ __('No sections added yet.') }}</p>
      @endif

      <hr>

      <h6 class="mb-2">{{ __('Add / Update Section') }}</h6>
      <form method="POST" action="{{ route('documents.sections.store', $document) }}">
        @csrf
        <div class="row">
          <div class="col-md-3 mb-2">
            <input class="form-control" name="key" value="{{ old('key') }}" placeholder="{{ __('key (e.g., scope)') }}" required>
          </div>
          <div class="col-md-5 mb-2">
            <input class="form-control" name="label" value="{{ old('label') }}" placeholder="{{ __('Label (e.g., Scope of Works)') }}" required>
          </div>
          <div class="col-md-2 mb-2">
            <input type="number" class="form-control" name="order" value="{{ old('order', 0) }}" placeholder="{{ __('Order') }}">
          </div>
          <div class="col-md-2 mb-2">
            <button class="btn btn-primary w-100" type="submit">{{ __('Save') }}</button>
          </div>
        </div>
        <textarea class="form-control" name="content" rows="4" placeholder="{{ __('Section content') }}">{{ old('content') }}</textarea>
      </form>
    </div>
  </div>
@endif

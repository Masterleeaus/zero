<div class="d-flex flex-wrap gap-2 mb-3">
  <button formaction="{{ route('documents.templates.apply') }}" formmethod="POST" class="btn btn-outline-dark">
    @csrf
    {{ __('Apply Template') }}
  </button>

  @if(View::exists('documents::documents.partials.ask_titanzero'))
    @include('documents::documents.partials.ask_titanzero', [
      'intent' => $intent ?? 'summarise_standard',
      'record_type' => $record_type ?? 'document',
      'record_id' => $record_id ?? null,
      'fields' => $fields ?? [],
    ])
  @endif
</div>

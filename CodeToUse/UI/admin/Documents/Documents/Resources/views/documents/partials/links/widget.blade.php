@php
  $links = $document->links()->orderByDesc('id')->take(10)->get();
@endphp

<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <strong>{{ __('Linked Records') }}</strong>
  </div>
  <div class="card-body">
    @can('link', $document)
      <form method="POST" action="{{ route('documents.links.store', $document) }}" class="row g-2 mb-3">
        @csrf
        <div class="col-md-4">
          <select name="linked_type" class="form-select form-select-sm" required>
            <option value="jobsite">{{ __('Jobsite') }}</option>
            <option value="job">{{ __('Job') }}</option>
            <option value="quote">{{ __('Quote') }}</option>
            <option value="invoice">{{ __('Invoice') }}</option>
          </select>
        </div>
        <div class="col-md-3">
          <input type="number" name="linked_id" class="form-control form-control-sm" placeholder="{{ __('ID') }}" required>
        </div>
        <div class="col-md-3">
          <input type="text" name="label" class="form-control form-control-sm" placeholder="{{ __('Label (optional)') }}">
        </div>
        <div class="col-md-2 d-grid">
          <button class="btn btn-outline-primary btn-sm" type="submit"><i class="ti ti-link"></i> {{ __('Link') }}</button>
        </div>
      </form>
    @endcan

    @if($links->count() === 0)
      <div class="text-muted">{{ __('No linked records yet.') }}</div>
    @else
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th>{{ __('Type') }}</th>
              <th>{{ __('ID') }}</th>
              <th>{{ __('Label') }}</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($links as $l)
              <tr>
                <td>{{ ucfirst($l->linked_type) }}</td>
                <td>{{ $l->linked_id }}</td>
                <td>{{ $l->label ?: '—' }}</td>
                <td class="text-end">
                  @can('link', $document)
                    <form method="POST" action="{{ route('documents.links.destroy', [$document, $l]) }}" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-outline-danger btn-sm" type="submit">{{ __('Unlink') }}</button>
                    </form>
                  @endcan
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>

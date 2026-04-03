@php
  $versions = $document->versions()->take(5)->get();
@endphp

<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <strong>{{ __('Recent Versions') }}</strong>
    <a class="btn btn-outline-dark btn-sm" href="{{ route('documents.versions.index', $document) }}">{{ __('View all') }}</a>
  </div>
  <div class="card-body">
    @if($versions->count() === 0)
      <div class="text-muted">{{ __('No versions yet.') }}</div>
    @else
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th>{{ __('Version') }}</th>
              <th>{{ __('Reason') }}</th>
              <th>{{ __('At') }}</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($versions as $v)
              <tr>
                <td>#{{ $v->version_no }}</td>
                <td>{{ $v->reason ?: '—' }}</td>
                <td>{{ $v->created_at?->format('Y-m-d H:i') }}</td>
                <td class="text-end">
                  <a class="btn btn-outline-secondary btn-sm" href="{{ route('documents.versions.show', [$document, $v]) }}">{{ __('View') }}</a>
                  @can('restore', $document)
                    <form method="POST" action="{{ route('documents.versions.restore', [$document, $v]) }}" class="d-inline">
                      @csrf
                      <button class="btn btn-outline-primary btn-sm" type="submit">{{ __('Restore') }}</button>
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

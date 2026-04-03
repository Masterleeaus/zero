<div class="card mt-3">
  <div class="card-body">
    <h5 class="mb-2">Share Links</h5>

    @can('documents.share')
      <form method="POST" action="{{ route('documents.sharelinks.store', $document->id) }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-4">
          <label class="form-label">Expires at (optional)</label>
          <input type="datetime-local" name="expires_at" class="form-control" />
        </div>
        <div class="col-md-3">
          <label class="form-label">Max views (optional)</label>
          <input type="number" name="max_views" class="form-control" min="1" />
        </div>
        <div class="col-md-3">
          <label class="form-label">Note</label>
          <input type="text" name="note" class="form-control" maxlength="255" />
        </div>
        <div class="col-md-2">
          <button class="btn btn-primary w-100">Create</button>
        </div>
      </form>

      @if(session('share_link_token'))
        <div class="alert alert-success mt-3">
          Share link created:
          <a href="{{ route('documents.share.public', session('share_link_token')) }}" target="_blank">
            {{ route('documents.share.public', session('share_link_token')) }}
          </a>
        </div>
      @endif
    @else
      <div class="text-muted">You don't have permission to create share links.</div>
    @endcan

    @php
      $links = \Modules\Documents\Entities\DocumentShareLink::query()
        ->where('document_id', $document->id)
        ->orderByDesc('id')
        ->limit(10)
        ->get();
    @endphp

    @if($links->count())
      <hr>
      <div class="table-responsive">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Link</th>
              <th>Expires</th>
              <th>Views</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($links as $l)
              <tr>
                <td>
                  <a href="{{ route('documents.share.public', $l->token) }}" target="_blank">Open</a>
                </td>
                <td>{{ $l->expires_at?->format('Y-m-d H:i') }}</td>
                <td>{{ $l->views_count ?? 0 }} @if($l->max_views) / {{ $l->max_views }} @endif</td>
                <td>
                  @if($l->revoked_at)
                    <span class="badge bg-danger">Revoked</span>
                  @elseif($l->expires_at && now()->greaterThan($l->expires_at))
                    <span class="badge bg-warning text-dark">Expired</span>
                  @else
                    <span class="badge bg-success">Active</span>
                  @endif
                </td>
                <td class="text-end">
                  @can('documents.share')
                    @if(!$l->revoked_at)
                      <form method="POST" action="{{ route('documents.sharelinks.revoke', [$document->id, $l->id]) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-danger">Revoke</button>
                      </form>
                    @endif
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

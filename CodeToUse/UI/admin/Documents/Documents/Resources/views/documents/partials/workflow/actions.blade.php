@php
  $status = $document->status ?? 'draft';
@endphp

<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <strong>{{ __('Workflow') }}</strong>
    @include('documents::documents.partials.status_badge', ['status' => $status])
  </div>
  <div class="card-body">
    <div class="d-flex flex-wrap gap-2">
      @can('update', $document)
        @if($status === 'draft')
          <form method="POST" action="{{ route('documents.workflow.review', $document) }}">
            @csrf
            <button class="btn btn-outline-primary btn-sm" type="submit"><i class="ti ti-send"></i> {{ __('Send for review') }}</button>
          </form>
        @endif
      @endcan

      @can('approve', $document)
        @if($status === 'review')
          <form method="POST" action="{{ route('documents.workflow.approve', $document) }}">
            @csrf
            <button class="btn btn-success btn-sm" type="submit"><i class="ti ti-check"></i> {{ __('Approve') }}</button>
          </form>
        @endif
      @endcan

      @can('archive', $document)
        @if($status !== 'archived')
          <form method="POST" action="{{ route('documents.workflow.archive', $document) }}">
            @csrf
            <button class="btn btn-outline-danger btn-sm" type="submit"><i class="ti ti-archive"></i> {{ __('Archive') }}</button>
          </form>
        @endif
      @endcan

      @can('update', $document)
        @if(in_array($status, ['review','approved','archived']))
          <form method="POST" action="{{ route('documents.workflow.draft', $document) }}">
            @csrf
            <button class="btn btn-outline-secondary btn-sm" type="submit"><i class="ti ti-edit"></i> {{ __('Back to draft') }}</button>
          </form>
        @endif
      @endcan

      <a class="btn btn-outline-dark btn-sm" href="{{ route('documents.versions.index', $document) }}">
        <i class="ti ti-history"></i> {{ __('Versions') }}
      </a>
    </div>

    @if(!empty($document->approved_at))
      <div class="text-muted small mt-3">
        {{ __('Approved at') }}: {{ optional($document->approved_at)->format('Y-m-d H:i') }}
      </div>
    @endif
    @if(!empty($document->archived_at))
      <div class="text-muted small">
        {{ __('Archived at') }}: {{ optional($document->archived_at)->format('Y-m-d H:i') }}
      </div>
    @endif
  </div>
</div>

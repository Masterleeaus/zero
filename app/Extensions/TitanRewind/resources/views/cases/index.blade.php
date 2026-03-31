@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="mb-1">Titan Rewind</h3>
      <div class="text-muted">Recovery supervisor for process rollback, cascade control, and manual review.</div>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('titanrewind.cases.index') }}" class="btn btn-outline-secondary">All Cases</a>
      <a href="{{ route('titanrewind.cases.manualReview') }}" class="btn btn-outline-danger">Manual Review</a>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted small">Cases</div><div class="fs-4">{{ $cases->total() }}</div></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted small">Open Conflicts</div><div class="fs-4">{{ $openConflicts ?? 0 }}</div></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted small">Conflict Hold Cases</div><div class="fs-4">{{ $conflictHoldCases ?? 0 }}</div></div></div></div>
  </div>

  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Title</th>
              <th>Status</th>
              <th>Severity</th>
              <th>Process</th>
              <th>Entity</th>
              <th>Updated</th>
            </tr>
          </thead>
          <tbody>
          @forelse($cases as $case)
            <tr>
              <td><a href="{{ route('titanrewind.cases.show', ['case' => $case->id]) }}">#{{ $case->id }}</a></td>
              <td>{{ $case->title }}</td>
              <td><span class="badge bg-{{ $case->status === 'conflict-hold' ? 'danger' : ($case->status === 'rolled-back' ? 'success' : 'secondary') }}">{{ $case->status }}</span></td>
              <td>{{ $case->severity }}</td>
              <td><code>{{ $case->process_id }}</code></td>
              <td><code>{{ $case->entity_type }}</code>@if($case->entity_id) #{{ $case->entity_id }} @endif</td>
              <td>{{ $case->updated_at }}</td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted py-4">No rewind cases found.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="mt-3">
    {{ $cases->links() }}
  </div>
</div>
@endsection

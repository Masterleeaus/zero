@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="mb-1">Case #{{ $case->id }} — {{ $case->title }}</h3>
      <div class="text-muted">
        Severity: <strong>{{ $case->severity }}</strong> · Status: <strong>{{ $case->status }}</strong> · Entity: <code>{{ $case->entity_type }}</code>@if($case->entity_id) #{{ $case->entity_id }} @endif
      </div>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('titanrewind.cases.index') }}" class="btn btn-outline-secondary">Back</a>
      <a href="{{ route('titanrewind.cases.timeline', ['case' => $case->id]) }}" class="btn btn-outline-primary">Timeline JSON</a>
      <a href="{{ route('titanrewind.cases.plan', ['case' => $case->id]) }}" class="btn btn-outline-dark">Plan JSON</a>
      <a href="{{ route('api.titanrewind.cases.snapshots', ['case' => $case->id]) }}" class="btn btn-outline-info">Snapshots JSON</a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="row g-3 mb-3">
    <div class="col-md-2"><div class="card"><div class="card-body"><div class="text-muted small">Timeline</div><div class="fs-4">{{ $historyBundle['counts']['timeline'] }}</div></div></div></div>
    <div class="col-md-2"><div class="card"><div class="card-body"><div class="text-muted small">Links</div><div class="fs-4">{{ $historyBundle['counts']['links'] }}</div></div></div></div>
    <div class="col-md-2"><div class="card"><div class="card-body"><div class="text-muted small">Actions</div><div class="fs-4">{{ $historyBundle['counts']['actions'] }}</div></div></div></div>
    <div class="col-md-2"><div class="card"><div class="card-body"><div class="text-muted small">Conflicts</div><div class="fs-4">{{ $historyBundle['counts']['conflicts'] }}</div></div></div></div>
    <div class="col-md-2"><div class="card"><div class="card-body"><div class="text-muted small">States</div><div class="fs-4">{{ $historyBundle['counts']['states'] }}</div></div></div></div>
    <div class="col-md-2"><div class="card"><div class="card-body"><div class="text-muted small">Plan Stages</div><div class="fs-4">{{ $historyBundle['counts']['plan_stages'] }}</div></div></div></div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Snapshots</div><div class="fs-4">{{ $historyBundle['counts']['snapshots'] }}</div></div></div></div>
    <div class="col-md-9"><div class="card"><div class="card-body small text-muted">Snapshots preserve before/after root and downstream rows for audit-grade rewind evidence and replay.</div></div></div></div>
  </div>

  <div class="row g-3">
    <div class="col-lg-5">
      <div class="card mb-3">
        <div class="card-header"><strong>Submit Correction</strong></div>
        <div class="card-body">
          <form method="POST" action="{{ route('titanrewind.cases.submitCorrection', ['case' => $case->id]) }}">
            @csrf
            <div class="mb-3"><label class="form-label">Correction process id</label><input name="process_id" class="form-control" placeholder="corr-{{ $case->id }}"></div>
            <div class="mb-3"><label class="form-label">Correction JSON</label><textarea name="correction_json" class="form-control" rows="7">{
  "fields_changed": ["customer_id"],
  "patch": {
    "customer_id": "new-value"
  }
}</textarea></div>
            <div class="form-check mb-3"><input class="form-check-input" type="checkbox" value="1" id="complete_now" name="complete_now"><label class="form-check-label" for="complete_now">Complete rollback immediately</label></div>
            <button class="btn btn-primary" type="submit">Submit Correction</button>
          </form>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header"><strong>Conflicts</strong></div>
        <div class="card-body">
          @forelse($case->conflicts as $conflict)
            <div class="border rounded p-2 mb-2">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div><strong>{{ $conflict->conflict_type }}</strong> <span class="badge bg-danger">{{ $conflict->severity }}</span></div>
                  <div class="text-muted small">{{ $conflict->message }}</div>
                  <div class="small mt-1">Status: <strong>{{ $conflict->status }}</strong></div>
                </div>
              </div>
              <div class="small text-muted mt-1">Hint: {{ $conflict->resolution_hint }}</div>
              @if($conflict->status === 'open')
              <form method="POST" action="{{ route('titanrewind.cases.conflicts.resolve', ['case' => $case->id, 'conflict' => $conflict->id]) }}" class="mt-2">
                @csrf
                <div class="input-group input-group-sm">
                  <select name="resolution" class="form-select">
                    <option value="approved">approved</option>
                    <option value="manual-review">manual-review</option>
                    <option value="rejected">rejected</option>
                  </select>
                  <input name="notes" class="form-control" placeholder="notes">
                  <button class="btn btn-outline-primary" type="submit">Update</button>
                </div>
              </form>
              @endif
            </div>
          @empty
            <div class="text-muted">No conflicts logged.</div>
          @endforelse
        </div>
      </div>

      <div class="card">
        <div class="card-header"><strong>Rollback</strong></div>
        <div class="card-body">
          <form method="POST" action="{{ route('titanrewind.cases.completeRollback', ['case' => $case->id]) }}" class="row g-2">
            @csrf
            <div class="col-6"><input name="correction_process_id" class="form-control" placeholder="correction_process_id" value="{{ $case->correction_process_id }}"></div>
            <div class="col-6"><input name="correction_entity_id" class="form-control" placeholder="correction_entity_id"></div>
            <div class="col-12"><button class="btn btn-success" type="submit">Complete Rollback</button></div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="card mb-3">
        <div class="card-header"><strong>Rollback Plan</strong></div>
        <div class="card-body">
          @forelse($historyBundle['rollback_plan']['stages'] as $stage)
            <div class="border rounded p-2 mb-2">
              <div><strong>Depth {{ $stage['depth'] }}</strong></div>
              <div class="small text-muted">Reuse: {{ count($stage['reuse']) }} · Reissue: {{ count($stage['reissue']) }} · Notify: {{ count($stage['notify']) }}</div>
            </div>
          @empty
            <div class="text-muted">No staged rollback plan built yet.</div>
          @endforelse
          @if(!empty($historyBundle['rollback_plan']['payment_plan']))
            <div class="mt-3">
              <strong>Payment Reissue Plan</strong>
              @foreach($historyBundle['rollback_plan']['payment_plan'] as $payment)
                <div class="small text-muted">{{ $payment['action'] }} for payment entity {{ $payment['entity_id'] ?? 'n/a' }}</div>
              @endforeach
            </div>
          @endif
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header"><strong>Process Snapshot</strong></div>
        <div class="card-body">
          <div class="small text-muted mb-2">Source process: <code>{{ $historyBundle['case']['process_id'] }}</code></div>
          <div class="small">Signals loaded: {{ count($historyBundle['process_snapshot']['signals'] ?? []) }} · States loaded: {{ count($historyBundle['process_snapshot']['states'] ?? []) }}</div>
          <div class="small text-muted">Dependencies: {{ count($historyBundle['process_snapshot']['dependencies']['parents'] ?? []) }} parents / {{ count($historyBundle['process_snapshot']['dependencies']['children'] ?? []) }} children</div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header"><strong>Snapshots</strong></div>
        <div class="card-body">
          @forelse($historyBundle['snapshots'] as $snapshot)
            <div class="border rounded p-2 mb-2">
              <div><strong>{{ $snapshot['snapshot_stage'] }}</strong> · {{ $snapshot['snapshot_scope'] }} · <code>{{ $snapshot['snapshot_key'] }}</code></div>
              <div class="text-muted small">{{ $snapshot['entity_type'] ?? 'process' }}{{ $snapshot['entity_id'] ? '#'.$snapshot['entity_id'] : '' }} · process {{ $snapshot['process_id'] ?? 'n/a' }} · {{ $snapshot['captured_at'] ?? 'n/a' }}</div>
              <div class="small mt-1">Source: {{ $snapshot['source_table'] ?? 'unresolved' }}</div>
            </div>
          @empty
            <div class="text-muted">No snapshots captured yet.</div>
          @endforelse
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header"><strong>Dependency Links</strong></div>
        <div class="card-body">
          @forelse($historyBundle['links'] as $link)
            <div class="border rounded p-2 mb-2">
              <div><strong>{{ $link['parent_entity_type'] ?? $case->entity_type }}</strong> → <strong>{{ $link['child_entity_type'] }}</strong></div>
              <div class="text-muted small">depth {{ $link['depth'] }} · {{ $link['relationship_type'] }} · status {{ $link['status'] }} · action {{ $link['action_required'] ?? 'n/a' }}</div>
            </div>
          @empty
            <div class="text-muted">No downstream links.</div>
          @endforelse
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header"><strong>Audit Timeline</strong></div>
        <div class="card-body">
          @forelse($timeline as $e)
            <div class="border rounded p-2 mb-2">
              <div class="d-flex justify-content-between">
                <div>
                  <div><strong>{{ $e['event_type'] }}</strong></div>
                  <div class="text-muted small">{{ $e['created_at'] }} · {{ $e['actor_type'] }}{{ $e['actor_id'] ? '#'.$e['actor_id'] : '' }}</div>
                </div>
                <div class="small text-muted text-end"><code>{{ substr($e['event_hash'], 0, 12) }}</code></div>
              </div>
            </div>
          @empty
            <div class="text-muted">No audit events.</div>
          @endforelse
        </div>
      </div>

      <div class="card">
        <div class="card-header"><strong>Actions</strong></div>
        <div class="card-body">
          @forelse($historyBundle['actions'] as $action)
            <div class="border rounded p-2 mb-2">
              <div><strong>{{ $action['action_type'] }}</strong> → {{ $action['target_type'] }}{{ $action['target_id'] ? '#'.$action['target_id'] : '' }}</div>
              <div class="text-muted small">{{ $action['executed_at'] }} · {{ $action['success'] ? 'success' : 'failed' }}</div>
            </div>
          @empty
            <div class="text-muted">No actions yet.</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

<div class="card mt-4">
  <div class="card-header">Replay Bundle</div>
  <div class="card-body">
    <p class="mb-2">JSON replay + deterministic history reconstruction for Zero/ops debugging.</p>
    <a class="btn btn-sm btn-outline-primary" href="{{ route('titanrewind.cases.replay', ['case' => $case->id]) }}">Open replay JSON</a>
  </div>
</div>

@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Titan Hello · Callback Inbox</h4>
    <div class="text-muted small">
      Showing {{ $callbacks->firstItem() ?? 0 }}–{{ $callbacks->lastItem() ?? 0 }} of {{ $callbacks->total() }}
    </div>
  </div>

  <form class="card card-body mb-3" method="get">
    <input type="hidden" name="company_id" value="{{ request('company_id') }}">
    <div class="row g-2">
      <div class="col-md-2">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="">Open</option>
          @foreach(['open','done','cancelled'] as $s)
            <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Priority</label>
        <select name="priority" class="form-select">
          <option value="">Any</option>
          @foreach(['low','normal','high','urgent'] as $p)
            <option value="{{ $p }}" @selected(request('priority')===$p)>{{ ucfirst($p) }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Due</label>
        <select name="due" class="form-select">
          <option value="">Any</option>
          <option value="today" @selected(request('due')==='today')>Today</option>
          <option value="overdue" @selected(request('due')==='overdue')>Overdue</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Assigned to (user id)</label>
        <input type="number" name="assigned_to" class="form-control" value="{{ request('assigned_to') }}">
      </div>
      <div class="col-md-3 d-flex align-items-end gap-2">
        <button class="btn btn-primary" type="submit">Filter</button>
        <a class="btn btn-outline-secondary" href="{{ route('titanhello.callbacks.index', ['company_id'=>request('company_id')]) }}">Reset</a>
      </div>
    </div>
  </form>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>Due</th>
            <th>From</th>
            <th>To</th>
            <th>Priority</th>
            <th>Assigned</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($callbacks as $cb)
            <tr>
              <td class="{{ $cb->due_at && $cb->due_at->isPast() && $cb->status==='open' ? 'text-danger fw-bold':'' }}">
                {{ optional($cb->due_at)->format('Y-m-d H:i') }}
              </td>
              <td>{{ $cb->from_number }}</td>
              <td>{{ $cb->to_number }}</td>
              <td>{{ ucfirst($cb->priority) }}</td>
              <td>{{ $cb->assigned_to ?: '—' }}</td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('titanhello.callbacks.show', ['company_id'=>request('company_id'),'id'=>$cb->id]) }}">Open</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">No callbacks.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-body">
      {{ $callbacks->links() }}
    </div>
  </div>
</div>
@endsection

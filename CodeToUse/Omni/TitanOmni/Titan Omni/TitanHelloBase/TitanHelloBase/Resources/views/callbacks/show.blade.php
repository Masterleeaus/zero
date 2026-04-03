@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-1">Callback #{{ $cb->id }}</h4>
      <div class="text-muted small">Status: <strong>{{ ucfirst($cb->status) }}</strong> · Priority: <strong>{{ ucfirst($cb->priority) }}</strong></div>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('titanhello.callbacks.index', ['company_id'=>request('company_id')]) }}">Back</a>
  </div>

  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card card-body">
        <h6>Caller</h6>
        <div><strong>From:</strong> {{ $cb->from_number }}</div>
        <div><strong>To:</strong> {{ $cb->to_number }}</div>
        <div class="mt-2"><strong>Due:</strong> {{ optional($cb->due_at)->format('Y-m-d H:i') }}</div>
        <div><strong>Assigned to:</strong> {{ $cb->assigned_to ?: '—' }}</div>
        @if($cb->note)
          <div class="mt-2"><strong>Note:</strong><br>{{ $cb->note }}</div>
        @endif
      </div>

      <div class="card card-body mt-3">
        <h6>Update</h6>
        <form method="post" action="{{ route('titanhello.callbacks.assign', ['company_id'=>request('company_id'),'id'=>$cb->id]) }}">
          @csrf
          <div class="mb-2">
            <label class="form-label">Assign to (user id)</label>
            <input type="number" name="assigned_to" class="form-control" value="{{ $cb->assigned_to }}">
          </div>
          <button class="btn btn-primary btn-sm">Assign</button>
        </form>

        <hr>

        <form method="post" action="{{ route('titanhello.callbacks.due', ['company_id'=>request('company_id'),'id'=>$cb->id]) }}">
          @csrf
          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label">Due at</label>
              <input type="datetime-local" name="due_at" class="form-control" value="{{ $cb->due_at ? $cb->due_at->format('Y-m-d\TH:i') : '' }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">Priority</label>
              <select name="priority" class="form-select">
                @foreach(['low','normal','high','urgent'] as $p)
                  <option value="{{ $p }}" @selected($cb->priority===$p)>{{ ucfirst($p) }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <button class="btn btn-outline-primary btn-sm mt-2">Update</button>
        </form>

        <hr>

        <div class="d-flex gap-2">
          <form method="post" action="{{ route('titanhello.callbacks.done', ['company_id'=>request('company_id'),'id'=>$cb->id]) }}">
            @csrf
            <button class="btn btn-success btn-sm" @disabled($cb->status!=='open')>Mark done</button>
          </form>
          <form method="post" action="{{ route('titanhello.callbacks.cancel', ['company_id'=>request('company_id'),'id'=>$cb->id]) }}">
            @csrf
            <button class="btn btn-danger btn-sm" @disabled($cb->status!=='open')>Cancel</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card card-body">
        <h6>Linked call</h6>
        @if($cb->call)
          <div><strong>Call ID:</strong> {{ $cb->call->id }}</div>
          <div><strong>Outcome:</strong> {{ $cb->call->call_outcome ?: '—' }}</div>
          <div><strong>Status:</strong> {{ $cb->call->status ?: '—' }}</div>
          <div class="mt-2">
            <a class="btn btn-outline-primary btn-sm" href="{{ route('titanhello.calls.show', ['company_id'=>request('company_id'),'id'=>$cb->call->id]) }}">Open call</a>
          </div>
        @else
          <div class="text-muted">No linked call.</div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

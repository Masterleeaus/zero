@extends('facility::layouts.master')
@section('content')
<div class="container py-3">
  <h2>Unit Timeline: {{ $unit->name ?? $unit->code }}</h2>
  <div class="card p-3">
    @if(empty($events))
      <em>No events yet.</em>
    @else
      <ul class="list-group">
        @foreach($events as $e)
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <div>
              <strong class="text-capitalize">{{ $e['type'] }}</strong>
              <div class="text-muted small">{{ $e['status'] }}</div>
            </div>
            <span class="badge bg-light text-dark">{{ \Carbon\Carbon::parse($e['at'])->toDayDateTimeString() }}</span>
          </li>
        @endforeach
      </ul>
    @endif
  </div>
</div>
@endsection

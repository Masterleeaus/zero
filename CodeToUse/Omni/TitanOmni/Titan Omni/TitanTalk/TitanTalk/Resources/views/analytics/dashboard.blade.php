@extends('titantalk::layouts.base')
@section('title','AIConverse — Analytics')
@section('body')
<div class="row g-3">
  <div class="col-md-3"><div class="card p-3"><div class="small text-muted">Conversations</div><div class="fs-4">{{ $conversations }}</div></div></div>
  <div class="col-md-3"><div class="card p-3"><div class="small text-muted">Messages (total)</div><div class="fs-4">{{ $messages }}</div></div></div>
  <div class="col-md-3"><div class="card p-3"><div class="small text-muted">Messages (today)</div><div class="fs-4">{{ $todayMsgs }}</div></div></div>
  <div class="col-md-3"><div class="card p-3"><div class="small text-muted">Avg Provider Latency (ms)</div><div class="fs-4">{{ number_format($latency ?? 0, 0) }}</div></div></div>
</div>
<div class="mt-4">
  <h5>Conversations by Channel</h5>
  <table class="table table-sm"><thead><tr><th>Channel</th><th>Conversations</th></tr></thead><tbody>
  @foreach($byChannel as $row)
    <tr><td>{{ $row->channel }}</td><td>{{ $row->c }}</td></tr>
  @endforeach
  </tbody></table>
</div>
@endsection

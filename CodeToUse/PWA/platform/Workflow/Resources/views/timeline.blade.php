@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="card-title">Workflow Timeline #{{ $workflowId }}</h4>
          @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
          <table class="table">
            <thead><tr><th>Time</th><th>Level</th><th>Message</th><th>Context</th></tr></thead>
            <tbody>
            @forelse($logs as $log)
              <tr>
                <td>{{ $log->created_at }}</td>
                <td>{{ strtoupper($log->level) }}</td>
                <td>{{ $log->message }}</td>
                <td><pre class="mb-0" style="white-space: pre-wrap;">{{ json_encode($log->context, JSON_PRETTY_PRINT) }}</pre></td>
              </tr>
            @empty
              <tr><td colspan="4">No events yet.</td></tr>
            @endforelse
            </tbody>
          </table>
          {{ $logs->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

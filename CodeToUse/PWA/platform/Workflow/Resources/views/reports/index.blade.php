@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="card mb-3">
        <div class="card-body">
          <h4 class="card-title">Workflow Reports</h4>
          <div class="row">
            <div class="col-md-3"><div class="h5 mb-0">Workflows</div><div class="display-6">{{ $stats['workflows_total'] }}</div></div>
            <div class="col-md-3"><div class="h5 mb-0">Steps</div><div class="display-6">{{ $stats['steps_total'] }}</div></div>
            <div class="col-md-3"><div class="h5 mb-0">Done</div><div class="display-6">{{ $stats['steps_done'] }}</div></div>
            <div class="col-md-3"><div class="h5 mb-0">Failed</div><div class="display-6 text-danger">{{ $stats['steps_failed'] }}</div></div>
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <h5>Average Step Completion Time</h5>
          <p class="lead">{{ $avg_seconds ? (round($avg_seconds,1).'s') : 'n/a' }}</p>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Top Failing Handlers</h5>
            <a class="btn btn-secondary btn-sm" href="{{ route('workflow.reports.export.csv') }}">Export Logs CSV</a>
          </div>
          <table class="table mt-3">
            <thead><tr><th>Handler</th><th>Failures</th></tr></thead>
            <tbody>
            @forelse($failures as $row)
              <tr>
                <td><code>{{ $row->handler }}</code></td>
                <td>{{ $row->cnt }}</td>
              </tr>
            @empty
              <tr><td colspan="2">No failures recorded.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

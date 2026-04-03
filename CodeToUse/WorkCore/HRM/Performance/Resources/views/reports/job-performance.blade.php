@extends('performance::layouts.master')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3">Reports: Job Performance</h4>

    <div class="card mb-3"><div class="card-body">
        <div class="row">
            <div class="col-md-3"><div class="p-2 border rounded">Count: <strong>{{ $report['count'] }}</strong></div></div>
            <div class="col-md-3"><div class="p-2 border rounded">Avg Overall: <strong>{{ $report['avg_overall'] }}</strong></div></div>
            <div class="col-md-3"><div class="p-2 border rounded">Avg Safety: <strong>{{ $report['avg_safety'] }}</strong></div></div>
            <div class="col-md-3"><div class="p-2 border rounded">Total Callbacks: <strong>{{ $report['total_callbacks'] }}</strong></div></div>
        </div>
        <div class="mt-3">
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('reports.export.job_performance_csv', $filters) }}">Export CSV</a>
        </div>
    </div></div>

    <div class="card"><div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead><tr>
                    <th>ID</th><th>Project</th><th>User</th><th>Overall</th><th>Safety</th><th>Callbacks</th><th>Status</th><th></th>
                </tr></thead>
                <tbody>
                @foreach($report['items'] as $s)
                    <tr>
                        <td>{{ $s->id }}</td>
                        <td>{{ $s->project_id }}</td>
                        <td>{{ $s->user_id }}</td>
                        <td>{{ $s->overall_score }}</td>
                        <td>{{ $s->safety_score }}</td>
                        <td>{{ $s->callback_count }}</td>
                        <td>{{ $s->status }}</td>
                        <td><a class="btn btn-sm btn-primary" href="{{ route('job-performance.show', $s->id) }}">View</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div></div>
</div>
@endsection

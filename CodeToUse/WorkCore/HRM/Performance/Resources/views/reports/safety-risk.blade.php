@extends('performance::layouts.master')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3">Reports: Safety Risk</h4>

    <div class="card mb-3"><div class="card-body">
        Count: <strong>{{ $report['count'] }}</strong> |
        Avg Safety: <strong>{{ $report['avg_safety'] }}</strong>
    </div></div>

    <div class="card"><div class="card-body">
        <h6>Lowest Safety Scores</h6>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead><tr><th>ID</th><th>Project</th><th>User</th><th>Safety</th><th></th></tr></thead>
                <tbody>
                @foreach($report['lowest'] as $s)
                    <tr>
                        <td>{{ $s->id }}</td>
                        <td>{{ $s->project_id }}</td>
                        <td>{{ $s->user_id }}</td>
                        <td>{{ $s->safety_score }}</td>
                        <td><a class="btn btn-sm btn-primary" href="{{ route('job-performance.show', $s->id) }}">View</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div></div>
</div>
@endsection

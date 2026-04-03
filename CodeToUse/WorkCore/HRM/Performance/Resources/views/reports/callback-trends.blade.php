@extends('performance::layouts.master')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3">Reports: Callback Trends</h4>

    <div class="card mb-3"><div class="card-body">
        Total callbacks: <strong>{{ $report['total_callbacks'] }}</strong> |
        Snapshots: <strong>{{ $report['count'] }}</strong>
        <div class="mt-2">
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('reports.export.callback_trends_csv', $filters) }}">Export CSV</a>
        </div>
    </div></div>

    <div class="card"><div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead><tr><th>Month</th><th>Callback Count</th></tr></thead>
                <tbody>
                @foreach($report['series'] as $month => $count)
                    <tr><td>{{ $month }}</td><td>{{ $count }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div></div>
</div>
@endsection

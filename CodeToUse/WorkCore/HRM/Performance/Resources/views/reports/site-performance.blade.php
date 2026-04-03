@extends('performance::layouts.master')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3">Reports: Site Performance</h4>

    <div class="card"><div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead><tr><th>Project/Site</th><th>Jobs</th><th>Avg Overall</th><th>Avg Safety</th><th>Callbacks</th></tr></thead>
                <tbody>
                @foreach($report['rows'] as $row)
                    <tr>
                        <td>{{ $row['project_id'] }}</td>
                        <td>{{ $row['jobs'] }}</td>
                        <td>{{ $row['avg_overall'] }}</td>
                        <td>{{ $row['avg_safety'] }}</td>
                        <td>{{ $row['callbacks'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div></div>
</div>
@endsection

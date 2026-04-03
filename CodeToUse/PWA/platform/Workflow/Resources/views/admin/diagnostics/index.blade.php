@extends('workflow::layouts.master')

@section('content')
<div class="content-wrapper">
    <div class="container-fluid">
        <h4 class="mb-3">Workflow Diagnostics</h4>
        <div class="card">
            <div class="card-body">
                <h6>Database tables</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Table</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tableStatus as $table => $ok)
                                <tr>
                                    <td>{{ $table }}</td>
                                    <td>
                                        @if($ok)
                                            <span class="badge bg-success">OK</span>
                                        @else
                                            <span class="badge bg-danger">Missing</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                
<hr class="my-4"/>

<h6>Module endpoints (canonical)</h6>
@if(!empty($endpoints))
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Route file</th>
                    <th>Method</th>
                    <th>Endpoint</th>
                </tr>
            </thead>
            <tbody>
                @foreach($endpoints as $ep)
                    <tr>
                        <td>{{ $ep['route_file'] ?? '' }}</td>
                        <td><code>{{ $ep['method'] ?? '' }}</code></td>
                        <td><code>{{ $ep['endpoint'] ?? '' }}</code></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <p class="text-muted mb-0">No endpoints registered.</p>
@endif

                <p class="mb-0 mt-3 text-muted">
                    This page is intentionally lightweight and must never break render.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

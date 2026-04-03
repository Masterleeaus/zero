@extends('workflow::layouts.master')

@section('content')
<div class="content-wrapper">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Run (System) #{{ $run->id }}</h4>
            <a href="{{ route('workflow.admin.workflows.runs.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div><strong>Status:</strong> {{ $run->status }}</div>
                <div><strong>Workflow ID:</strong> {{ $run->workflow_id }}</div>
                <div><strong>Trigger:</strong> {{ $run->trigger_event ?? '-' }}</div>
                <div><strong>Created:</strong> {{ $run->created_at }}</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><strong>Steps</strong></div>
            <div class="card-body">
                @if(($run->steps ?? collect())->count() === 0)
                    <p class="mb-0">No steps recorded.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Action</th>
                                    <th>Status</th>
                                    <th>Started</th>
                                    <th>Finished</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($run->steps as $step)
                                    <tr>
                                        <td>{{ $step->id }}</td>
                                        <td>{{ $step->action_key }}</td>
                                        <td>{{ $step->status }}</td>
                                        <td>{{ $step->started_at }}</td>
                                        <td>{{ $step->finished_at }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

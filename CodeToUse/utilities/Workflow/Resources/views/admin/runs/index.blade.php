@extends('workflow::layouts.master')

@section('content')
<div class="content-wrapper">
    <div class="container-fluid">
        <h4 class="mb-3">Workflow Runs (System)</h4>
        <div class="card">
            <div class="card-body">
                @if($runs->count() === 0)
                    <p class="mb-0">No runs yet.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Workflow</th>
                                    <th>Status</th>
                                    <th>Triggered At</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($runs as $run)
                                    <tr>
                                        <td>{{ $run->id }}</td>
                                        <td>{{ $run->workflow_id }}</td>
                                        <td>{{ $run->status }}</td>
                                        <td>{{ $run->created_at }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('workflow.admin.workflows.runs.show', $run->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $runs->links() }}
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

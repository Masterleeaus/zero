@extends('default.panel.layout.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Repair Templates</h1>
        <a href="{{ route('repair.templates.create') }}" class="btn btn-primary">New Template</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Equipment Type</th>
                        <th>Fault Type</th>
                        <th>Est. Duration</th>
                        <th>Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                        <tr>
                            <td>
                                <a href="{{ route('repair.templates.show', $template) }}">{{ $template->name }}</a>
                            </td>
                            <td>{{ $template->template_category ?? '—' }}</td>
                            <td>{{ $template->equipment_type ?? '—' }}</td>
                            <td>{{ $template->fault_type ?? '—' }}</td>
                            <td>{{ $template->estimated_duration ? $template->estimated_duration . ' min' : '—' }}</td>
                            <td>
                                @if($template->active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('repair.templates.edit', $template) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No repair templates found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($templates->hasPages())
            <div class="card-footer">{{ $templates->links() }}</div>
        @endif
    </div>
</div>
@endsection

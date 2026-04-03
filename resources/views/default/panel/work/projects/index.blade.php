@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Field Service Projects'))

@section('content')
<div class="container-xl">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">{{ __('Field Service Projects') }}</h2>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProjectModal">
                    {{ __('New Project') }}
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>{{ __('Project') }}</th>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Premises') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Start') }}</th>
                        <th>{{ __('End') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                        <tr>
                            <td class="fw-bold">{{ $project->name }}</td>
                            <td>{{ $project->reference ?? '—' }}</td>
                            <td>{{ $project->customer?->name ?? '—' }}</td>
                            <td>{{ $project->premises?->name ?? '—' }}</td>
                            <td>
                                <span class="badge bg-{{ match($project->status) {
                                    'active' => 'blue',
                                    'completed' => 'green',
                                    'on_hold' => 'yellow',
                                    'cancelled' => 'red',
                                    default => 'secondary'
                                } }}-lt">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span>
                            </td>
                            <td>{{ $project->planned_start?->format('d M Y') ?? '—' }}</td>
                            <td>{{ $project->planned_end?->format('d M Y') ?? '—' }}</td>
                            <td>
                                <a href="{{ route('work.projects.show', $project->id) }}" class="btn btn-sm btn-outline-primary">
                                    {{ __('View') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">{{ __('No projects found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($projects->hasPages())
            <div class="card-footer">{{ $projects->links() }}</div>
        @endif
    </div>
</div>

{{-- Create Project Modal --}}
<div class="modal modal-blur fade" id="createProjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('work.projects.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('New Project') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label required">{{ __('Project Name') }}</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Reference') }}</label>
                            <input type="text" name="reference" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Planned Start') }}</label>
                            <input type="date" name="planned_start" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Planned End') }}</label>
                            <input type="date" name="planned_end" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Create Project') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

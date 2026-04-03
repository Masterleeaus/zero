@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', $project->name)

@section('content')
<div class="container-xl">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">{{ $project->name }}</h2>
                <div class="text-muted mt-1">
                    @if($project->reference) <span class="me-2">{{ $project->reference }}</span> @endif
                    <span class="badge bg-{{ match($project->status) {
                        'active' => 'blue', 'completed' => 'green', 'on_hold' => 'yellow', 'cancelled' => 'red', default => 'secondary'
                    } }}-lt">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span>
                </div>
            </div>
            <div class="col-auto">
                <a href="{{ route('work.projects.index') }}" class="btn btn-outline-secondary">
                    {{ __('Back to Projects') }}
                </a>
            </div>
        </div>
    </div>

    @php $progress = $project->executionProgress(); @endphp

    <div class="row row-cards">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">{{ __('Project Details') }}</h3>
                    <dl class="row">
                        <dt class="col-sm-5">{{ __('Customer') }}</dt>
                        <dd class="col-sm-7">{{ $project->customer?->name ?? '—' }}</dd>

                        <dt class="col-sm-5">{{ __('Premises') }}</dt>
                        <dd class="col-sm-7">{{ $project->premises?->name ?? '—' }}</dd>

                        <dt class="col-sm-5">{{ __('Team') }}</dt>
                        <dd class="col-sm-7">{{ $project->team?->name ?? '—' }}</dd>

                        <dt class="col-sm-5">{{ __('Assigned To') }}</dt>
                        <dd class="col-sm-7">{{ $project->assignedUser?->name ?? '—' }}</dd>

                        <dt class="col-sm-5">{{ __('Planned Start') }}</dt>
                        <dd class="col-sm-7">{{ $project->planned_start?->format('d M Y') ?? '—' }}</dd>

                        <dt class="col-sm-5">{{ __('Planned End') }}</dt>
                        <dd class="col-sm-7">{{ $project->planned_end?->format('d M Y') ?? '—' }}</dd>

                        <dt class="col-sm-5">{{ __('Est. Hours') }}</dt>
                        <dd class="col-sm-7">{{ $project->estimated_hours ?? '—' }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Progress --}}
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">{{ __('Execution Progress') }}</h3>
                    <div class="mb-2">
                        <div class="progress mb-1">
                            <div class="progress-bar bg-blue" style="width: {{ $progress['percent_complete'] }}%"></div>
                        </div>
                        <div class="text-muted small">{{ $progress['completed_jobs'] }} / {{ $progress['total_jobs'] }} {{ __('jobs completed') }} ({{ $progress['percent_complete'] }}%)</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            {{-- Service Jobs --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Service Jobs') }}</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>{{ __('Job') }}</th>
                                <th>{{ __('Stage') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Scheduled') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($project->serviceJobs as $job)
                                <tr>
                                    <td>{{ $job->title ?? '#'.$job->id }}</td>
                                    <td>{{ $job->stage?->name ?? '—' }}</td>
                                    <td><span class="badge bg-blue-lt">{{ ucfirst($job->status) }}</span></td>
                                    <td>{{ $job->portalScheduleLabel() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">{{ __('No jobs linked.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Link Job --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Link a Service Job') }}</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('work.projects.link-job', $project->id) }}">
                        @csrf
                        <div class="row g-2 align-items-end">
                            <div class="col">
                                <label class="form-label">{{ __('Job ID') }}</label>
                                <input type="number" name="job_id" class="form-control" placeholder="{{ __('Enter job ID') }}" required>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">{{ __('Link Job') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

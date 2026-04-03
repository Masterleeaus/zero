@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('My Service Jobs'))

@section('content')
<div class="container-xl">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">{{ __('Service Jobs') }}</h2>
                <div class="text-muted mt-1">{{ $customer->name }}</div>
            </div>
            <div class="col-auto">
                <a href="{{ route('portal.service.index') }}" class="btn btn-outline-secondary">
                    {{ __('Back to Dashboard') }}
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>{{ __('Job') }}</th>
                        <th>{{ __('Premises') }}</th>
                        <th>{{ __('Stage') }}</th>
                        <th>{{ __('Scheduled') }}</th>
                        <th>{{ __('Assigned To') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobs as $job)
                        <tr>
                            <td>{{ $job->title ?? 'Job #'.$job->id }}</td>
                            <td>{{ $job->premises?->name ?? '—' }}</td>
                            <td>{{ $job->stage?->name ?? '—' }}</td>
                            <td>{{ $job->portalScheduleLabel() }}</td>
                            <td>{{ $job->assignedUser?->name ?? '—' }}</td>
                            <td><span class="badge bg-blue-lt">{{ $job->portalStatusLabel() }}</span></td>
                            <td>
                                <a href="{{ route('portal.service.jobs.show', $job->id) }}" class="btn btn-sm btn-outline-primary">
                                    {{ __('View') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">{{ __('No service jobs found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($jobs->hasPages())
            <div class="card-footer">{{ $jobs->links() }}</div>
        @endif
    </div>
</div>
@endsection

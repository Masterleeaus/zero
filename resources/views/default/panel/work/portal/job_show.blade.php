@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Service Job Detail'))

@section('content')
<div class="container-xl">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">{{ $job->title ?? 'Job #'.$job->id }}</h2>
                <div class="text-muted mt-1">{{ $job->portalStatusLabel() }}</div>
            </div>
            <div class="col-auto">
                <a href="{{ route('portal.service.jobs') }}" class="btn btn-outline-secondary">
                    {{ __('Back to Jobs') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">{{ __('Status') }}</dt>
                        <dd class="col-sm-8"><span class="badge bg-blue-lt">{{ $job->portalStatusLabel() }}</span></dd>

                        <dt class="col-sm-4">{{ __('Stage') }}</dt>
                        <dd class="col-sm-8">{{ $job->stage?->name ?? '—' }}</dd>

                        <dt class="col-sm-4">{{ __('Scheduled') }}</dt>
                        <dd class="col-sm-8">{{ $job->portalScheduleLabel() }}</dd>

                        <dt class="col-sm-4">{{ __('Premises') }}</dt>
                        <dd class="col-sm-8">{{ $job->premises?->name ?? '—' }}</dd>

                        <dt class="col-sm-4">{{ __('Assigned To') }}</dt>
                        <dd class="col-sm-8">{{ $job->assignedUser?->name ?? '—' }}</dd>

                        @if($job->notes)
                        <dt class="col-sm-4">{{ __('Notes') }}</dt>
                        <dd class="col-sm-8">{{ $job->notes }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

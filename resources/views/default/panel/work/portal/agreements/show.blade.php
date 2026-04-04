@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Service Contract'))

@section('content')
<div class="container-xl">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">{{ $agreement->reference ?? '#'.$agreement->id }}</div>
                <h2 class="page-title">{{ $agreement->title ?? __('Service Contract') }}</h2>
            </div>
            <div class="col-auto">
                <a href="{{ route('portal.service.agreements') }}" class="btn btn-outline-secondary">{{ __('Back') }}</a>
            </div>
        </div>
    </div>

    <div class="row row-cards">
        {{-- Agreement details --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Contract Details') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <label class="form-label text-muted">{{ __('Status') }}</label>
                            @php
                                $badgeClass = match($agreement->status) {
                                    'active'    => 'bg-green-lt',
                                    'expired'   => 'bg-red-lt',
                                    'cancelled' => 'bg-secondary',
                                    'renewed'   => 'bg-blue-lt',
                                    'suspended' => 'bg-yellow-lt',
                                    default     => 'bg-secondary',
                                };
                            @endphp
                            <div><span class="badge {{ $badgeClass }}">{{ ucfirst($agreement->status) }}</span></div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="form-label text-muted">{{ __('Service Frequency') }}</label>
                            <div>{{ ucfirst($agreement->service_frequency ?? '—') }}</div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="form-label text-muted">{{ __('Start Date') }}</label>
                            <div>{{ $agreement->start_date?->format('d M Y') ?? '—' }}</div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="form-label text-muted">{{ __('End Date') }}</label>
                            <div>{{ $agreement->end_date?->format('d M Y') ?? '—' }}</div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="form-label text-muted">{{ __('Billing Cycle') }}</label>
                            <div>{{ ucfirst($agreement->billing_cycle ?? '—') }}</div>
                        </div>
                        @if($agreement->premises)
                        <div class="col-sm-6 mb-3">
                            <label class="form-label text-muted">{{ __('Premises') }}</label>
                            <div>{{ $agreement->premises->name ?? $agreement->premises->address ?? '—' }}</div>
                        </div>
                        @endif
                    </div>
                    @if($agreement->notes)
                    <div class="mt-2">
                        <label class="form-label text-muted">{{ __('Notes') }}</label>
                        <div class="text-muted">{{ $agreement->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Execution summary --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Execution Summary') }}</h3>
                </div>
                <div class="card-body">
                    @php $summary = $agreement->executionSummary(); @endphp
                    <div class="row g-2 align-items-center">
                        <div class="col-6 text-center">
                            <div class="h1 mb-0">{{ $summary['total_jobs'] }}</div>
                            <div class="text-muted">{{ __('Jobs') }}</div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="h1 mb-0">{{ $summary['completed_jobs'] }}</div>
                            <div class="text-muted">{{ __('Completed') }}</div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="h1 mb-0">{{ $summary['total_visits'] }}</div>
                            <div class="text-muted">{{ __('Visits') }}</div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="h1 mb-0">{{ $summary['completed_visits'] }}</div>
                            <div class="text-muted">{{ __('Done') }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('portal.service.fsm-agreements.visits', $agreement) }}"
                       class="btn btn-sm btn-outline-primary w-100">{{ __('View Visits') }}</a>
                    <a href="{{ route('portal.service.fsm-agreements.invoices', $agreement) }}"
                       class="btn btn-sm btn-outline-secondary w-100 mt-2">{{ __('View Invoices') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

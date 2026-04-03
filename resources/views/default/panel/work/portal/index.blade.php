@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('My Service Portal'))

@section('content')
<div class="container-xl">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-auto">
                <h2 class="page-title">{{ __('Service Portal') }}</h2>
                <div class="text-muted mt-1">{{ __('Welcome back,') }} {{ $customer->name }}</div>
            </div>
        </div>
    </div>

    <div class="row row-cards">
        {{-- Upcoming Visits --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Upcoming Visits') }}</h3>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($upcomingVisits as $visit)
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="badge bg-blue">{{ $visit->portalStatusLabel() }}</span>
                                </div>
                                <div class="col">
                                    <div class="fw-bold">{{ $visit->getSchedulableTitle() }}</div>
                                    <div class="text-muted small">{{ $visit->portalScheduleLabel() }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-muted">{{ __('No upcoming visits.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Recent Jobs --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Recent Service Jobs') }}</h3>
                    <div class="card-options">
                        <a href="{{ route('portal.service.jobs') }}" class="btn btn-sm btn-outline-secondary">{{ __('View All') }}</a>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($recentJobs as $job)
                        <a href="{{ route('portal.service.jobs.show', $job->id) }}" class="list-group-item list-group-item-action">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="badge bg-green">{{ $job->portalStatusLabel() }}</span>
                                </div>
                                <div class="col">
                                    <div class="fw-bold">{{ $job->title ?? 'Job #'.$job->id }}</div>
                                    <div class="text-muted small">{{ $job->portalScheduleLabel() }}</div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="list-group-item text-muted">{{ __('No recent jobs.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Open Quotes --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Quotes') }}</h3>
                    <div class="card-options">
                        <a href="{{ route('portal.service.quotes') }}" class="btn btn-sm btn-outline-secondary">{{ __('View All') }}</a>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($openQuotes as $quote)
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col">
                                    <div class="fw-bold">{{ $quote->reference ?? '#'.$quote->id }}</div>
                                    <div class="text-muted small">{{ ucfirst($quote->status) }}</div>
                                </div>
                                <div class="col-auto text-end">
                                    <div class="fw-bold">{{ $quote->total ?? '—' }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-muted">{{ __('No open quotes.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Unpaid Invoices --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Unpaid Invoices') }}</h3>
                    <div class="card-options">
                        <a href="{{ route('portal.service.invoices') }}" class="btn btn-sm btn-outline-secondary">{{ __('View All') }}</a>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($unpaidInvoices as $invoice)
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col">
                                    <div class="fw-bold">{{ $invoice->reference ?? '#'.$invoice->id }}</div>
                                    <div class="text-muted small">{{ ucfirst($invoice->status) }}</div>
                                </div>
                                <div class="col-auto text-end">
                                    <div class="fw-bold">{{ $invoice->total ?? '—' }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-muted">{{ __('No unpaid invoices.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

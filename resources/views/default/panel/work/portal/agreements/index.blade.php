@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('My Service Contracts'))

@section('content')
<div class="container-xl">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">{{ __('Service Contracts') }}</h2>
                <div class="text-muted mt-1">{{ __('Active and upcoming field service agreements') }}</div>
            </div>
            <div class="col-auto">
                <a href="{{ route('portal.service.index') }}" class="btn btn-outline-secondary">{{ __('Back') }}</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Frequency') }}</th>
                        <th>{{ __('Start') }}</th>
                        <th>{{ __('End') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agreements as $agreement)
                        <tr>
                            <td class="text-muted">{{ $agreement->reference ?? '#'.$agreement->id }}</td>
                            <td>{{ $agreement->title ?? __('Service Contract') }}</td>
                            <td>
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
                                <span class="badge {{ $badgeClass }}">{{ ucfirst($agreement->status) }}</span>
                            </td>
                            <td>{{ ucfirst($agreement->service_frequency ?? '—') }}</td>
                            <td>{{ $agreement->start_date?->format('d M Y') ?? '—' }}</td>
                            <td>{{ $agreement->end_date?->format('d M Y') ?? '—' }}</td>
                            <td>
                                <a href="{{ route('portal.service.fsm-agreements.show', $agreement) }}"
                                   class="btn btn-sm btn-outline-primary">{{ __('View') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                {{ __('No service contracts found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

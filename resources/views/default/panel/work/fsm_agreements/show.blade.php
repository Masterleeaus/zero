@extends('panel.layout.app')
@section('title', $agreement->title ?? __('Field Service Agreement'))

@section('content')
    <div class="py-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-muted text-sm">{{ $agreement->reference ?? '#'.$agreement->id }}</div>
                <h2 class="text-lg font-semibold">{{ $agreement->title ?? __('Field Service Agreement') }}</h2>
            </div>
            <div class="flex gap-2">
                @if($agreement->status === 'active')
                    <form method="POST" action="{{ route('dashboard.work.fsm-agreements.renew', $agreement) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm"
                                onclick="return confirm('{{ __('Renew this agreement?') }}')">
                            {{ __('Renew') }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('dashboard.work.fsm-agreements.terminate', $agreement) }}">
                        @csrf
                        <input type="hidden" name="reason" value="{{ __('Terminated via dashboard') }}">
                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                onclick="return confirm('{{ __('Terminate this agreement?') }}')">
                            {{ __('Terminate') }}
                        </button>
                    </form>
                @endif
                <a href="{{ route('dashboard.work.fsm-agreements.index') }}" class="btn btn-outline-secondary btn-sm">
                    {{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Details --}}
            <div class="md:col-span-2">
                <x-card>
                    <x-slot:header>{{ __('Agreement Details') }}</x-slot:header>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-muted text-sm">{{ __('Status') }}</div>
                            <div>
                                @php
                                    $badgeClass = match($agreement->status) {
                                        'active'    => 'badge-success',
                                        'expired'   => 'badge-danger',
                                        'cancelled' => 'badge-secondary',
                                        'renewed'   => 'badge-info',
                                        'suspended' => 'badge-warning',
                                        default     => 'badge-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ ucfirst($agreement->status) }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="text-muted text-sm">{{ __('Customer') }}</div>
                            <div>{{ $agreement->customer?->name ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-muted text-sm">{{ __('Service Frequency') }}</div>
                            <div>{{ ucfirst($agreement->service_frequency ?? '—') }}</div>
                        </div>
                        <div>
                            <div class="text-muted text-sm">{{ __('Billing Cycle') }}</div>
                            <div>{{ ucfirst($agreement->billing_cycle ?? '—') }}</div>
                        </div>
                        <div>
                            <div class="text-muted text-sm">{{ __('Start Date') }}</div>
                            <div>{{ $agreement->start_date?->format('d M Y') ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-muted text-sm">{{ __('End Date') }}</div>
                            <div>{{ $agreement->end_date?->format('d M Y') ?? '—' }}</div>
                        </div>
                        @if($agreement->premises)
                        <div>
                            <div class="text-muted text-sm">{{ __('Premises') }}</div>
                            <div>{{ $agreement->premises->name ?? $agreement->premises->address ?? '—' }}</div>
                        </div>
                        @endif
                        @if($agreement->quote)
                        <div>
                            <div class="text-muted text-sm">{{ __('Originating Quote') }}</div>
                            <div>{{ $agreement->quote->title ?? '#'.$agreement->quote->id }}</div>
                        </div>
                        @endif
                    </div>
                </x-card>
            </div>

            {{-- Execution summary --}}
            <div>
                <x-card>
                    <x-slot:header>{{ __('Execution Summary') }}</x-slot:header>
                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div>
                            <div class="text-2xl font-bold">{{ $summary['total_jobs'] }}</div>
                            <div class="text-muted text-sm">{{ __('Total Jobs') }}</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-green-600">{{ $summary['completed_jobs'] }}</div>
                            <div class="text-muted text-sm">{{ __('Completed') }}</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ $summary['total_visits'] }}</div>
                            <div class="text-muted text-sm">{{ __('Visits') }}</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-green-600">{{ $summary['completed_visits'] }}</div>
                            <div class="text-muted text-sm">{{ __('Done') }}</div>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>

        {{-- Recent visits --}}
        @if($agreement->visits->isNotEmpty())
        <x-card>
            <x-slot:header>{{ __('Scheduled Visits') }}</x-slot:header>
            <x-table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('Scheduled') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Job') }}</th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @foreach($agreement->visits->take(10) as $visit)
                        <tr>
                            <td>{{ $visit->scheduled_date?->format('d M Y') ?? '—' }}</td>
                            <td>{{ ucfirst($visit->visit_type ?? '—') }}</td>
                            <td><span class="badge badge-secondary">{{ ucfirst($visit->status) }}</span></td>
                            <td>{{ $visit->service_job_id ? '#'.$visit->service_job_id : '—' }}</td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-table>
        </x-card>
        @endif
    </div>
@endsection

@extends('panel.layout.app')
@section('title', __('Field Service Agreements'))

@section('content')
    <div class="py-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">{{ __('Field Service Agreements') }}</h2>
        </div>

        <x-card>
            <x-table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Frequency') }}</th>
                        <th>{{ __('Start') }}</th>
                        <th>{{ __('End') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @forelse($agreements as $agreement)
                        <tr>
                            <td class="text-muted">{{ $agreement->reference ?? '#'.$agreement->id }}</td>
                            <td>
                                <a class="text-primary-600"
                                   href="{{ route('dashboard.work.fsm-agreements.show', $agreement) }}">
                                    {{ $agreement->title ?? __('Service Contract') }}
                                </a>
                            </td>
                            <td>{{ $agreement->customer?->name ?? '—' }}</td>
                            <td>{{ ucfirst($agreement->service_frequency ?? '—') }}</td>
                            <td>{{ $agreement->start_date?->format('d M Y') ?? '—' }}</td>
                            <td>{{ $agreement->end_date?->format('d M Y') ?? '—' }}</td>
                            <td>
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
                            </td>
                            <td>
                                <a href="{{ route('dashboard.work.fsm-agreements.show', $agreement) }}"
                                   class="btn btn-sm btn-outline-secondary">{{ __('View') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">{{ __('No field service agreements found.') }}</td>
                        </tr>
                    @endforelse
                </x-slot:body>
            </x-table>
            {{ $agreements->links() }}
        </x-card>
    </div>
@endsection

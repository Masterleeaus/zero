@extends('panel.layout.app')
@section('title', $route->name)
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.work.routes.edit', $route) }}" variant="secondary">
        {{ __('Edit') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-6">

        {{-- Route details ------------------------------------------------- --}}
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">{{ __('Technician') }}</p>
                <p class="font-medium">{{ $route->assignedUser?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">{{ __('Team') }}</p>
                <p class="font-medium">{{ $route->team?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">{{ __('Status') }}</p>
                <p class="font-medium">{{ ucfirst($route->status) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">{{ __('Max Stops/Day') }}</p>
                <p class="font-medium">{{ $route->max_stops_per_day ?: __('Unlimited') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">{{ __('Active Days') }}</p>
                <p class="font-medium">
                    @php
                        $dayNames = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
                        $days = [];
                        foreach ($dayNames as $i => $name) {
                            if ($route->active_days_mask & (1 << $i)) {
                                $days[] = $name;
                            }
                        }
                    @endphp
                    {{ implode(', ', $days) ?: '—' }}
                </p>
            </div>
            @if($route->notes)
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500">{{ __('Notes') }}</p>
                    <p class="whitespace-pre-line">{{ $route->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Recent day-route runs ----------------------------------------- --}}
        <div>
            <h2 class="text-base font-semibold mb-3">{{ __('Recent Day-Route Runs') }}</h2>
            <x-table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Technician') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Stops') }}</th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @forelse($route->routeStops as $stop)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($stop->route_date)->format('d M Y') }}</td>
                            <td>{{ $stop->assignedUser?->name ?? '—' }}</td>
                            <td>{{ ucfirst($stop->status) }}</td>
                            <td>{{ $stop->stopItems()->count() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-6 text-gray-500">{{ __('No day-route runs yet.') }}</td>
                        </tr>
                    @endforelse
                </x-slot:body>
            </x-table>
        </div>

    </div>
@endsection

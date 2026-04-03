@extends('panel.layout.app')
@section('title', __('Vehicles'))

@section('content')
    <div class="py-6 space-y-4">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-semibold">{{ __('Vehicles') }}</h1>
            <x-button href="{{ route('dashboard.work.vehicles.create') }}">{{ __('Add Vehicle') }}</x-button>
        </div>
        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Registration') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Team') }}</th>
                    <th>{{ __('Driver') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($vehicles as $vehicle)
                    <tr>
                        <td>{{ $vehicle->name }}</td>
                        <td>{{ $vehicle->registration ?? '-' }}</td>
                        <td>{{ $vehicle->vehicle_type }}</td>
                        <td>{{ $vehicle->team?->name ?? '-' }}</td>
                        <td>{{ $vehicle->assignedDriver?->name ?? '-' }}</td>
                        <td><x-badge>{{ $vehicle->status }}</x-badge></td>
                        <td class="text-end">
                            <x-button size="sm" href="{{ route('dashboard.work.vehicles.show', $vehicle) }}">{{ __('View') }}</x-button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">{{ __('No vehicles found.') }}</td></tr>
                @endforelse
            </x-slot:body>
        </x-table>
        {{ $vehicles->links() }}
    </div>
@endsection

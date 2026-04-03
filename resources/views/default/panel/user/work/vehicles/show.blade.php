@extends('panel.layout.app')
@section('title', $vehicle->name)

@section('content')
    <div class="py-6 space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-semibold">{{ $vehicle->name }}</h1>
            <x-button href="{{ route('dashboard.work.vehicles.edit', $vehicle) }}">{{ __('Edit') }}</x-button>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><span class="font-medium">{{ __('Registration') }}:</span> {{ $vehicle->registration ?? '-' }}</div>
            <div><span class="font-medium">{{ __('Type') }}:</span> {{ $vehicle->vehicle_type }}</div>
            <div><span class="font-medium">{{ __('Team') }}:</span> {{ $vehicle->team?->name ?? '-' }}</div>
            <div><span class="font-medium">{{ __('Driver') }}:</span> {{ $vehicle->assignedDriver?->name ?? '-' }}</div>
            <div><span class="font-medium">{{ __('Status') }}:</span> <x-badge>{{ $vehicle->status }}</x-badge></div>
            <div><span class="font-medium">{{ __('Capacity') }}:</span> {{ $vehicle->capacity_kg ? $vehicle->capacity_kg . ' kg' : '-' }}</div>
        </div>

        @if($vehicle->stockItems->count())
        <div>
            <h2 class="font-semibold mb-2">{{ __('Onboard Stock') }}</h2>
            <x-table>
                <x-slot:head>
                    <tr><th>{{ __('Item') }}</th><th>{{ __('SKU') }}</th><th>{{ __('Qty') }}</th><th>{{ __('Status') }}</th></tr>
                </x-slot:head>
                <x-slot:body>
                    @foreach($vehicle->stockItems as $stock)
                    <tr>
                        <td>{{ $stock->item_name }}</td>
                        <td>{{ $stock->sku ?? '-' }}</td>
                        <td>{{ $stock->quantity }}</td>
                        <td><x-badge>{{ $stock->status }}</x-badge></td>
                    </tr>
                    @endforeach
                </x-slot:body>
            </x-table>
        </div>
        @endif

        @if($vehicle->vehicleEquipment->count())
        <div>
            <h2 class="font-semibold mb-2">{{ __('Equipment') }}</h2>
            <x-table>
                <x-slot:head>
                    <tr><th>{{ __('Item') }}</th><th>{{ __('Qty') }}</th><th>{{ __('Condition') }}</th></tr>
                </x-slot:head>
                <x-slot:body>
                    @foreach($vehicle->vehicleEquipment as $eq)
                    <tr>
                        <td>{{ $eq->equipment_label ?? $eq->equipment?->name ?? '-' }}</td>
                        <td>{{ $eq->quantity }}</td>
                        <td>{{ $eq->condition }}</td>
                    </tr>
                    @endforeach
                </x-slot:body>
            </x-table>
        </div>
        @endif
    </div>
@endsection

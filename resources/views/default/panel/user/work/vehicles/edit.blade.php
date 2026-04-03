@extends('panel.layout.app')
@section('title', __('Edit Vehicle'))

@section('content')
    <div class="py-6 max-w-2xl">
        <h1 class="text-xl font-semibold mb-4">{{ __('Edit Vehicle') }}: {{ $vehicle->name }}</h1>
        <form method="POST" action="{{ route('dashboard.work.vehicles.update', $vehicle) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <x-label for="name">{{ __('Vehicle Name') }}</x-label>
                <x-input id="name" name="name" value="{{ old('name', $vehicle->name) }}" required />
            </div>
            <div>
                <x-label for="registration">{{ __('Registration') }}</x-label>
                <x-input id="registration" name="registration" value="{{ old('registration', $vehicle->registration) }}" />
            </div>
            <div>
                <x-label for="vehicle_type">{{ __('Type') }}</x-label>
                <x-select id="vehicle_type" name="vehicle_type">
                    @foreach(['van','truck','car','motorcycle','trailer','other'] as $type)
                        <option value="{{ $type }}" @selected(old('vehicle_type', $vehicle->vehicle_type) === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-label for="status">{{ __('Status') }}</x-label>
                <x-select id="status" name="status">
                    @foreach(['active','in_use','servicing','retired'] as $s)
                        <option value="{{ $s }}" @selected(old('status', $vehicle->status) === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-label for="notes">{{ __('Notes') }}</x-label>
                <x-textarea id="notes" name="notes">{{ old('notes', $vehicle->notes) }}</x-textarea>
            </div>
            <x-button type="submit">{{ __('Update Vehicle') }}</x-button>
        </form>
    </div>
@endsection

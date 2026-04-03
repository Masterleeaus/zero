@extends('panel.layout.app')
@section('title', __('Add Vehicle'))

@section('content')
    <div class="py-6 max-w-2xl">
        <h1 class="text-xl font-semibold mb-4">{{ __('Add Vehicle') }}</h1>
        <form method="POST" action="{{ route('dashboard.work.vehicles.store') }}" class="space-y-4">
            @csrf
            <div>
                <x-label for="name">{{ __('Vehicle Name') }}</x-label>
                <x-input id="name" name="name" value="{{ old('name') }}" required />
            </div>
            <div>
                <x-label for="registration">{{ __('Registration') }}</x-label>
                <x-input id="registration" name="registration" value="{{ old('registration') }}" />
            </div>
            <div>
                <x-label for="vehicle_type">{{ __('Type') }}</x-label>
                <x-select id="vehicle_type" name="vehicle_type">
                    @foreach(['van','truck','car','motorcycle','trailer','other'] as $type)
                        <option value="{{ $type }}" @selected(old('vehicle_type') === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-label for="notes">{{ __('Notes') }}</x-label>
                <x-textarea id="notes" name="notes">{{ old('notes') }}</x-textarea>
            </div>
            <x-button type="submit">{{ __('Save Vehicle') }}</x-button>
        </form>
    </div>
@endsection

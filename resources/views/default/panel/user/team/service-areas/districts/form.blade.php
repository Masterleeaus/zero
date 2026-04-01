@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('District') }}</p>
                <h1 class="text-2xl font-semibold">{{ $district ? __('Edit District') : __('Add District') }}</h1>
            </div>
        </div>

        <x-card>
            <form method="POST"
                  action="{{ $district ? route('dashboard.team.service-area-districts.update', $district) : route('dashboard.team.service-area-districts.store') }}"
                  class="space-y-4">
                @csrf
                @if($district)
                    @method('PUT')
                @endif

                <x-input name="name" label="{{ __('Name') }}" value="{{ old('name', $district?->name) }}" required />
                <x-input name="description" label="{{ __('Description') }}" value="{{ old('description', $district?->description) }}" />

                @if($regions->isNotEmpty())
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Region') }}</label>
                        <select name="region_id" class="w-full border-slate-300 rounded-md shadow-sm text-sm">
                            <option value="">— {{ __('None') }} —</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}" @selected(old('region_id', $district?->region_id) == $region->id)>{{ $region->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex gap-3">
                    <x-button type="submit">
                        <x-tabler-check class="size-4" />
                        {{ __('Save') }}
                    </x-button>
                    <x-button variant="ghost" href="{{ route('dashboard.team.service-area-districts.index') }}">
                        {{ __('Cancel') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection

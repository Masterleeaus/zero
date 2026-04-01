@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Region') }}</p>
                <h1 class="text-2xl font-semibold">{{ $region ? __('Edit Region') : __('Add Region') }}</h1>
            </div>
        </div>

        <x-card>
            <form method="POST"
                  action="{{ $region ? route('dashboard.team.service-area-regions.update', $region) : route('dashboard.team.service-area-regions.store') }}"
                  class="space-y-4">
                @csrf
                @if($region)
                    @method('PUT')
                @endif

                <x-input name="name" label="{{ __('Name') }}" value="{{ old('name', $region?->name) }}" required />
                <x-input name="description" label="{{ __('Description') }}" value="{{ old('description', $region?->description) }}" />

                <div class="flex gap-3">
                    <x-button type="submit">
                        <x-tabler-check class="size-4" />
                        {{ __('Save') }}
                    </x-button>
                    <x-button variant="ghost" href="{{ route('dashboard.team.service-area-regions.index') }}">
                        {{ __('Cancel') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection

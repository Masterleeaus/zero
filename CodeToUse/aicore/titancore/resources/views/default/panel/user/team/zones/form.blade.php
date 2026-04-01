@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Zone') }}</p>
                <h1 class="text-2xl font-semibold">
                    {{ $zone ? __('Edit Zone') : __('Add Zone') }}
                </h1>
            </div>
        </div>

        <x-card>
            <form class="space-y-4">
                <x-input name="name" label="{{ __('Name') }}" value="{{ $zone['name'] ?? '' }}" />
                <x-input name="code" label="{{ __('Code') }}" value="{{ $zone['code'] ?? '' }}" />
                <x-input name="sites" type="number" label="{{ __('Sites') }}" value="{{ $zone['sites'] ?? '' }}" />

                <div class="flex gap-3">
                    <x-button type="submit">
                        <x-tabler-check class="size-4" />
                        {{ __('Save') }}
                    </x-button>
                    <x-button variant="ghost" href="{{ route('dashboard.team.zones.index') }}">
                        {{ __('Cancel') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection

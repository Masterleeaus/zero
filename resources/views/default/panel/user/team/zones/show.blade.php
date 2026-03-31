@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Zone') }}</p>
                <h1 class="text-2xl font-semibold">{{ $zone['name'] ?? __('Zone') }}</h1>
                <p class="text-slate-500">{{ __('Code') }}: {{ $zone['code'] ?? '' }}</p>
            </div>
        </div>

        <x-card>
            <p class="text-sm text-slate-500">{{ __('Sites') }}</p>
            <p class="font-semibold">{{ $zone['sites'] ?? 0 }}</p>
        </x-card>
    </div>
@endsection


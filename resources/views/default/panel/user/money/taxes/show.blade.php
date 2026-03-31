@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Tax') }}</p>
                <h1 class="text-2xl font-semibold">{{ $tax['name'] ?? __('Tax') }}</h1>
                <p class="text-slate-500">{{ __('Rate') }}: {{ $tax['rate'] ?? 0 }}%</p>
            </div>
            @if($tax['default'] ?? false)
                <x-badge variant="info">{{ __('Default') }}</x-badge>
            @endif
        </div>
    </div>
@endsection

@extends('default.layout.app')
@section('content')
    <div class="max-w-4xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Deal') }}</p>
                <h1 class="text-2xl font-semibold">{{ $deal['title'] ?? __('Deal') }}</h1>
                <p class="text-slate-500">{{ __('Customer') }}: {{ $deal['customer'] ?? __('N/A') }}</p>
            </div>
            <x-badge variant="info">{{ ucfirst($deal['stage'] ?? 'prospecting') }}</x-badge>
        </div>

        <x-card>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-slate-500">{{ __('Owner') }}</p>
                    <p class="font-semibold">{{ $deal['owner'] ?? __('Unassigned') }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">{{ __('Value') }}</p>
                    <p class="font-semibold">${{ number_format($deal['value'] ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">{{ __('Last Update') }}</p>
                    <p class="font-semibold">{{ $deal['updated_at'] ?? '—' }}</p>
                </div>
            </div>
        </x-card>
    </div>
@endsection

@extends('default.layout.app')
@section('content')
    <div class="max-w-4xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Credit Note') }}</p>
                <h1 class="text-2xl font-semibold">{{ $creditNote['number'] ?? __('Credit Note') }}</h1>
                <p class="text-slate-500">{{ __('Customer') }}: {{ $creditNote['customer'] ?? __('N/A') }}</p>
            </div>
            <x-badge variant="info">{{ ucfirst($creditNote['status'] ?? 'draft') }}</x-badge>
        </div>

        <x-card>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-slate-500">{{ __('Issued At') }}</p>
                    <p class="font-semibold">{{ $creditNote['issued_at'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">{{ __('Total') }}</p>
                    <p class="font-semibold">${{ number_format($creditNote['total'] ?? 0, 2) }}</p>
                </div>
            </div>
        </x-card>
    </div>
@endsection

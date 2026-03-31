@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Bank Account') }}</p>
                <h1 class="text-2xl font-semibold">{{ $account['name'] ?? __('Account') }}</h1>
                <p class="text-slate-500">{{ $account['bank'] ?? '' }} · ****{{ $account['last4'] ?? '----' }}</p>
            </div>
            @if($account['default'] ?? false)
                <x-badge variant="info">{{ __('Default') }}</x-badge>
            @endif
        </div>

        <x-card>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-slate-500">{{ __('Currency') }}</p>
                    <p class="font-semibold">{{ $account['currency'] ?? 'USD' }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">{{ __('Status') }}</p>
                    <p class="font-semibold">{{ ($account['default'] ?? false) ? __('Default') : __('Secondary') }}</p>
                </div>
            </div>
        </x-card>
    </div>
@endsection


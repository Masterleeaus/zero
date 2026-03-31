@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Bank Account') }}</p>
                <h1 class="text-2xl font-semibold">
                    {{ $account ? __('Edit Account') : __('Add Account') }}
                </h1>
            </div>
        </div>

        <x-card>
            <form class="space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <x-input name="name" label="{{ __('Account Name') }}" value="{{ $account['name'] ?? '' }}" />
                    <x-input name="bank" label="{{ __('Bank') }}" value="{{ $account['bank'] ?? '' }}" />
                    <x-input name="last4" label="{{ __('Last 4 digits') }}" value="{{ $account['last4'] ?? '' }}" />
                    <x-input name="currency" label="{{ __('Currency') }}" value="{{ $account['currency'] ?? 'USD' }}" />
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" class="form-checkbox" {{ ($account['default'] ?? false) ? 'checked' : '' }}>
                    {{ __('Set as default payout account') }}
                </label>

                <div class="flex gap-3">
                    <x-button type="submit">
                        <x-tabler-check class="size-4" />
                        {{ __('Save') }}
                    </x-button>
                    <x-button variant="ghost" href="{{ route('dashboard.money.bank-accounts.index') }}">
                        {{ __('Cancel') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection


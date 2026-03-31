@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Tax') }}</p>
                <h1 class="text-2xl font-semibold">
                    {{ $tax ? __('Edit Tax') : __('Add Tax') }}
                </h1>
            </div>
        </div>

        <x-card>
            <form class="space-y-4">
                <x-input name="name" label="{{ __('Name') }}" value="{{ $tax['name'] ?? '' }}" />
                <x-input name="rate" type="number" step="0.01" label="{{ __('Rate (%)') }}" value="{{ $tax['rate'] ?? '' }}" />
                <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" class="form-checkbox" {{ ($tax['default'] ?? false) ? 'checked' : '' }}>
                    {{ __('Make default tax') }}
                </label>

                <div class="flex gap-3">
                    <x-button type="submit">
                        <x-tabler-check class="size-4" />
                        {{ __('Save') }}
                    </x-button>
                    <x-button variant="ghost" href="{{ route('dashboard.money.taxes.index') }}">
                        {{ __('Cancel') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection


@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Deal') }}</p>
                <h1 class="text-2xl font-semibold">
                    {{ $deal ? __('Edit Deal') : __('New Deal') }}
                </h1>
            </div>
        </div>

        <x-card>
            <form class="space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <x-input name="title" label="{{ __('Title') }}" value="{{ $deal['title'] ?? '' }}" />
                    <x-input name="customer" label="{{ __('Customer') }}" value="{{ $deal['customer'] ?? '' }}" />
                    <x-input name="owner" label="{{ __('Owner') }}" value="{{ $deal['owner'] ?? '' }}" />
                    <x-select name="stage" label="{{ __('Stage') }}">
                        @foreach($stages as $stage)
                            <option value="{{ $stage }}" @selected(($deal['stage'] ?? '') === $stage)>{{ ucfirst($stage) }}</option>
                        @endforeach
                    </x-select>
                    <x-input name="value" type="number" step="0.01" label="{{ __('Value') }}" value="{{ $deal['value'] ?? '' }}" />
                </div>
                <x-textarea name="notes" label="{{ __('Notes') }}">{{ data_get($deal, 'notes', '') }}</x-textarea>
                <div class="flex gap-3">
                    <x-button type="submit">
                        <x-tabler-check class="size-4" />
                        {{ __('Save') }}
                    </x-button>
                    <x-button variant="ghost" href="{{ route('dashboard.crm.deals.index') }}">
                        {{ __('Cancel') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection

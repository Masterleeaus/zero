@extends('default.layout.app')
@section('content')
    <div class="max-w-4xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Quote Template') }}</p>
                <h1 class="text-2xl font-semibold">
                    {{ $template ? __('Edit Template') : __('New Template') }}
                </h1>
            </div>
        </div>

        <x-card>
            <form class="space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <x-input name="name" label="{{ __('Name') }}" value="{{ $template['name'] ?? '' }}" />
                    <x-input name="category" label="{{ __('Category') }}" value="{{ $template['category'] ?? '' }}" />
                </div>

                <x-line-item-editor :items="$items" name="items" />

                <div class="flex gap-3">
                    <x-button type="submit">
                        <x-tabler-check class="size-4" />
                        {{ __('Save') }}
                    </x-button>
                    <x-button variant="ghost" href="{{ route('dashboard.money.quote-templates.index') }}">
                        {{ __('Cancel') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection


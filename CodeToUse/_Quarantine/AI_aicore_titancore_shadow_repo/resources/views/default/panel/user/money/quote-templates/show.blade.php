@extends('default.layout.app')
@section('content')
    <div class="max-w-4xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Quote Template') }}</p>
                <h1 class="text-2xl font-semibold">{{ $template['name'] ?? __('Template') }}</h1>
                <p class="text-slate-500">{{ __('Category') }}: {{ $template['category'] ?? __('N/A') }}</p>
            </div>
        </div>

        <x-card>
            <p class="text-sm text-slate-500">{{ __('Last updated') }}</p>
            <p class="font-semibold">{{ $template['updated_at'] ?? '—' }}</p>
            <p class="text-sm text-slate-500 mt-4">{{ __('Line items in this template') }}: {{ $template['items'] ?? 0 }}</p>
        </x-card>
    </div>
@endsection

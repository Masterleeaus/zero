@extends('panel.layout.app')
@section('title', __('Job Profitability'))

@section('content')
    <div class="py-6 space-y-6">
        <h1 class="text-xl font-semibold">{{ __('Job Profitability') }}</h1>

        <form method="get" class="flex gap-2 items-end flex-wrap">
            <x-form.group>
                <x-form.label for="from">{{ __('From') }}</x-form.label>
                <x-form.input type="date" id="from" name="from" value="{{ $from->toDateString() }}" />
            </x-form.group>
            <x-form.group>
                <x-form.label for="to">{{ __('To') }}</x-form.label>
                <x-form.input type="date" id="to" name="to" value="{{ $to->toDateString() }}" />
            </x-form.group>
            <x-button type="submit">{{ __('Apply') }}</x-button>
        </form>

        {{-- Period summary --}}
        <div class="bg-white border rounded p-5">
            <h2 class="font-semibold text-gray-700 mb-3">{{ __('Period Summary') }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-500">{{ __('Total Revenue') }}</p>
                    <p class="text-xl font-bold text-green-700">{{ number_format($byPeriod['gross_revenue'], 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Total Cost') }}</p>
                    <p class="text-xl font-bold text-red-600">{{ number_format($byPeriod['gross_cost'], 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Gross Margin') }}</p>
                    <p class="text-xl font-bold {{ $byPeriod['gross_margin'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                        {{ number_format($byPeriod['gross_margin'], 2) }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Margin %') }}</p>
                    <p class="text-xl font-bold {{ $byPeriod['margin_pct'] >= 10 ? 'text-green-700' : 'text-red-600' }}">
                        {{ $byPeriod['margin_pct'] }}%
                    </p>
                </div>
            </div>
        </div>

        <p class="text-sm text-gray-500">
            {{ __('Period') }}: {{ $from->toDateString() }} – {{ $to->toDateString() }}
        </p>

        <div class="flex gap-3">
            <a href="{{ route('dashboard.money.profitability.index') }}" class="text-sm text-blue-600 hover:underline">{{ __('Detailed Profitability Reports') }}</a>
        </div>
    </div>
@endsection

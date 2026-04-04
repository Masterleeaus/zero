@extends('panel.layout.app')
@section('title', __('Profit & Loss'))

@section('content')
    <div class="py-6 space-y-6">
        <h1 class="text-xl font-semibold">{{ __('Profit & Loss') }}</h1>

        <form method="get" class="flex gap-2 items-end flex-wrap">
            <x-form.group>
                <x-form.label for="period_start">{{ __('From') }}</x-form.label>
                <x-form.input type="date" id="period_start" name="period_start" value="{{ $periodStart }}" />
            </x-form.group>
            <x-form.group>
                <x-form.label for="period_end">{{ __('To') }}</x-form.label>
                <x-form.input type="date" id="period_end" name="period_end" value="{{ $periodEnd }}" />
            </x-form.group>
            <x-button type="submit">{{ __('Run Report') }}</x-button>
        </form>

        <div class="bg-white border rounded divide-y">
            <div class="px-4 py-3 flex justify-between">
                <span class="font-medium">{{ __('Revenue / Income') }}</span>
                <span class="font-semibold">{{ number_format($report['income'], 2) }}</span>
            </div>
            <div class="px-4 py-3 flex justify-between">
                <span>{{ __('Cost of Goods / Job Costs') }}</span>
                <span class="text-red-600">{{ number_format($report['cost_of_goods'], 2) }}</span>
            </div>
            <div class="px-4 py-3 flex justify-between bg-gray-50">
                <span class="font-medium">{{ __('Gross Profit') }}</span>
                <span class="font-semibold {{ $report['gross_profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($report['gross_profit'], 2) }}</span>
            </div>
            <div class="px-4 py-3 flex justify-between">
                <span>{{ __('Operating Expenses') }}</span>
                <span class="text-red-600">{{ number_format($report['expenses'], 2) }}</span>
            </div>
            <div class="px-4 py-3 flex justify-between bg-blue-50">
                <span class="font-bold text-lg">{{ __('Net Profit') }}</span>
                <span class="font-bold text-lg {{ $report['net_profit'] >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ number_format($report['net_profit'], 2) }}</span>
            </div>
        </div>
    </div>
@endsection

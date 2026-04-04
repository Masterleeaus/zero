@extends('panel.layout.app')
@section('title', __('Cash Flow'))

@section('content')
    <div class="py-6 space-y-6">
        <h1 class="text-xl font-semibold">{{ __('Cash Flow') }}</h1>

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
                <span class="font-medium">{{ __('Cash In (payments received)') }}</span>
                <span class="font-semibold text-green-600">{{ number_format($report['cash_in'], 2) }}</span>
            </div>
            <div class="px-4 py-3 flex justify-between">
                <span>{{ __('Cash Out (expenses + payroll)') }}</span>
                <span class="text-red-600">{{ number_format($report['cash_out'], 2) }}</span>
            </div>
            <div class="px-4 py-3 flex justify-between bg-blue-50">
                <span class="font-bold text-lg">{{ __('Net Cash Flow') }}</span>
                <span class="font-bold text-lg {{ $report['net_cash'] >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ number_format($report['net_cash'], 2) }}</span>
            </div>
        </div>
    </div>
@endsection

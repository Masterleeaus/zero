@extends('panel.layout.app')
@section('title', __('Balance Sheet'))

@section('content')
    <div class="py-6 space-y-6">
        <h1 class="text-xl font-semibold">{{ __('Balance Sheet') }}</h1>

        <form method="get" class="flex gap-2 items-end">
            <x-form.group>
                <x-form.label for="as_at">{{ __('As at') }}</x-form.label>
                <x-form.input type="date" id="as_at" name="as_at" value="{{ $asAt }}" />
            </x-form.group>
            <x-button type="submit">{{ __('Run Report') }}</x-button>
        </form>

        <div class="bg-white border rounded divide-y">
            <div class="px-4 py-3 flex justify-between">
                <span class="font-medium">{{ __('Total Assets') }}</span>
                <span class="font-semibold text-blue-600">{{ number_format($report['total_assets'], 2) }}</span>
            </div>
            <div class="px-4 py-3 flex justify-between">
                <span>{{ __('Total Liabilities') }}</span>
                <span class="text-red-600">{{ number_format($report['total_liabilities'], 2) }}</span>
            </div>
            <div class="px-4 py-3 flex justify-between">
                <span>{{ __('Total Equity') }}</span>
                <span class="text-purple-600">{{ number_format($report['total_equity'], 2) }}</span>
            </div>
            <div class="px-4 py-3 flex justify-between bg-gray-50">
                <span class="font-bold">{{ __('Liabilities + Equity') }}</span>
                <span class="font-bold">{{ number_format($report['total_liabilities'] + $report['total_equity'], 2) }}</span>
            </div>
        </div>

        @php $balanced = abs($report['total_assets'] - ($report['total_liabilities'] + $report['total_equity'])) < 0.01; @endphp
        @if(! $balanced)
            <x-alert variant="warning">{{ __('Balance sheet is not in balance. Ensure all transactions have been posted to the ledger.') }}</x-alert>
        @endif
    </div>
@endsection

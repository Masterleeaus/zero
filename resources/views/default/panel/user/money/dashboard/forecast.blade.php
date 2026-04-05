@extends('panel.layout.app')
@section('title', __('Financial Forecast'))

@section('content')
    <div class="py-6 space-y-6">
        <h1 class="text-xl font-semibold">{{ __('Financial Forecast') }}</h1>

        @foreach([
            ['label' => __('30-Day Forecast'), 'data' => $forecast30],
            ['label' => __('90-Day Forecast'), 'data' => $forecast90],
            ['label' => __('12-Month Forecast'), 'data' => $forecast12m],
        ] as $block)
        <div class="bg-white border rounded p-5">
            <h2 class="font-semibold text-gray-700 mb-4">{{ $block['label'] }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-500">{{ __('Revenue Forecast') }}</p>
                    <p class="text-xl font-bold text-green-700">{{ number_format($block['data']['revenue_forecast'], 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Cost Forecast') }}</p>
                    <p class="text-xl font-bold text-red-600">{{ number_format($block['data']['cost_forecast'], 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Margin Forecast') }}</p>
                    <p class="text-xl font-bold {{ $block['data']['margin_forecast'] >= 0 ? 'text-green-700' : 'text-red-600' }}">
                        {{ number_format($block['data']['margin_forecast'], 2) }}
                        <span class="text-sm font-normal">({{ $block['data']['margin_pct_forecast'] }}%)</span>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Cash Runway') }}</p>
                    <p class="text-xl font-bold {{ ($block['data']['cash_runway_estimate'] ?? 999) >= 30 ? 'text-green-700' : 'text-red-600' }}">
                        @if($block['data']['cash_runway_estimate'] !== null)
                            {{ $block['data']['cash_runway_estimate'] }} {{ __('days') }}
                        @else
                            {{ __('N/A') }}
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Pending Liabilities') }}</p>
                    <p class="text-lg font-semibold text-orange-600">{{ number_format($block['data']['pending_liabilities'], 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Daily Revenue Trend') }}</p>
                    <p class="text-lg font-semibold text-blue-700">{{ number_format($block['data']['daily_revenue_trend'], 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Daily Cost Trend') }}</p>
                    <p class="text-lg font-semibold text-gray-700">{{ number_format($block['data']['daily_cost_trend'], 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Generated') }}</p>
                    <p class="text-sm text-gray-500">{{ $block['data']['generated_at'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endsection

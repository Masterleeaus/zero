@extends('panel.layout.app')
@section('title', __('Financial Dashboard'))

@section('content')
    <div class="py-6 space-y-6">
        <h1 class="text-xl font-semibold">{{ __('Financial Dashboard') }}</h1>

        {{-- Snapshot widgets --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white border rounded p-4">
                <p class="text-sm text-gray-500">{{ __('Cash On Hand') }}</p>
                <p class="text-2xl font-bold text-green-700">{{ number_format($snapshot['cash_on_hand'], 2) }}</p>
            </div>
            <div class="bg-white border rounded p-4">
                <p class="text-sm text-gray-500">{{ __('Receivables') }}</p>
                <p class="text-2xl font-bold text-blue-700">{{ number_format($snapshot['receivables_total'], 2) }}</p>
            </div>
            <div class="bg-white border rounded p-4">
                <p class="text-sm text-gray-500">{{ __('Payables') }}</p>
                <p class="text-2xl font-bold text-red-700">{{ number_format($snapshot['payables_total'], 2) }}</p>
            </div>
            <div class="bg-white border rounded p-4">
                <p class="text-sm text-gray-500">{{ __('Gross Margin Est.') }}</p>
                <p class="text-2xl font-bold {{ $snapshot['gross_margin_estimate'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                    {{ number_format($snapshot['gross_margin_estimate'], 2) }}
                </p>
            </div>
            <div class="bg-white border rounded p-4">
                <p class="text-sm text-gray-500">{{ __('Wages Liability') }}</p>
                <p class="text-xl font-semibold text-orange-600">{{ number_format($snapshot['wages_liability_estimate'], 2) }}</p>
            </div>
            <div class="bg-white border rounded p-4">
                <p class="text-sm text-gray-500">{{ __('Supplier Liability') }}</p>
                <p class="text-xl font-semibold text-orange-600">{{ number_format($snapshot['supplier_liability'], 2) }}</p>
            </div>
            <div class="bg-white border rounded p-4">
                <p class="text-sm text-gray-500">{{ __('Job Cost Outstanding') }}</p>
                <p class="text-xl font-semibold text-gray-700">{{ number_format($snapshot['job_cost_outstanding'], 2) }}</p>
            </div>
            <div class="bg-white border rounded p-4">
                <p class="text-sm text-gray-500">{{ __('Unbilled Work Est.') }}</p>
                <p class="text-xl font-semibold text-indigo-700">{{ number_format($snapshot['unbilled_work_estimate'], 2) }}</p>
            </div>
        </div>

        {{-- KPI tiles --}}
        <h2 class="text-lg font-semibold mt-4">{{ __('30-Day KPIs') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white border rounded p-4">
                <p class="text-sm text-gray-500">{{ __('Gross Margin %') }}</p>
                <p class="text-2xl font-bold {{ ($kpis['gross_margin_pct'] ?? 0) >= 10 ? 'text-green-700' : 'text-red-600' }}">
                    {{ $kpis['gross_margin_pct'] ?? '—' }}%
                </p>
            </div>
            <div class="bg-white border rounded p-4">
                <p class="text-sm text-gray-500">{{ __('Net Margin %') }}</p>
                <p class="text-2xl font-bold {{ ($kpis['net_margin_pct'] ?? 0) >= 5 ? 'text-green-700' : 'text-red-600' }}">
                    {{ $kpis['net_margin_pct'] ?? '—' }}%
                </p>
            </div>
            <div class="bg-white border rounded p-4">
                <p class="text-sm text-gray-500">{{ __('Cash Buffer Days') }}</p>
                <p class="text-2xl font-bold {{ ($kpis['cash_buffer_days'] ?? 0) >= 30 ? 'text-green-700' : 'text-red-600' }}">
                    {{ $kpis['cash_buffer_days'] ?? '—' }}
                </p>
            </div>
            <div class="bg-white border rounded p-4">
                <p class="text-sm text-gray-500">{{ __('Revenue / Job') }}</p>
                <p class="text-xl font-semibold text-gray-700">
                    {{ $kpis['revenue_per_job'] !== null ? number_format($kpis['revenue_per_job'], 2) : '—' }}
                </p>
            </div>
        </div>

        {{-- Quick links --}}
        <div class="flex gap-3 flex-wrap mt-2">
            <a href="{{ route('dashboard.money.cashflow.index') }}" class="text-sm text-blue-600 hover:underline">{{ __('Cash Flow') }}</a>
            <a href="{{ route('dashboard.money.forecast.index') }}" class="text-sm text-blue-600 hover:underline">{{ __('Forecast') }}</a>
            <a href="{{ route('dashboard.money.kpis.index') }}" class="text-sm text-blue-600 hover:underline">{{ __('KPIs') }}</a>
            <a href="{{ route('dashboard.money.job-profitability.index') }}" class="text-sm text-blue-600 hover:underline">{{ __('Job Profitability') }}</a>
        </div>

        <p class="text-xs text-gray-400">{{ __('Snapshot as at') }}: {{ $snapshot['as_at'] }}</p>
    </div>
@endsection

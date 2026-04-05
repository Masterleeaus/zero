@extends('panel.layout.app')
@section('title', __('Financial KPIs'))

@section('content')
    <div class="py-6 space-y-6">
        <h1 class="text-xl font-semibold">{{ __('Financial KPIs') }}</h1>

        <form method="get" class="flex gap-2 items-end flex-wrap">
            <x-form.group>
                <x-form.label for="from">{{ __('From') }}</x-form.label>
                <x-form.input type="date" id="from" name="from" value="{{ $kpis['period_start'] }}" />
            </x-form.group>
            <x-form.group>
                <x-form.label for="to">{{ __('To') }}</x-form.label>
                <x-form.input type="date" id="to" name="to" value="{{ $kpis['period_end'] }}" />
            </x-form.group>
            <x-button type="submit">{{ __('Apply') }}</x-button>
        </form>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div class="bg-white border rounded p-4">
                <p class="text-sm text-gray-500">{{ __('Gross Margin %') }}</p>
                <p class="text-3xl font-bold {{ ($kpis['gross_margin_pct'] ?? 0) >= 10 ? 'text-green-700' : 'text-red-600' }}">
                    {{ $kpis['gross_margin_pct'] ?? '—' }}%
                </p>
            </div>
            <div class="bg-white border rounded p-4">
                <p class="text-sm text-gray-500">{{ __('Net Margin %') }}</p>
                <p class="text-3xl font-bold {{ ($kpis['net_margin_pct'] ?? 0) >= 5 ? 'text-green-700' : 'text-red-600' }}">
                    {{ $kpis['net_margin_pct'] ?? '—' }}%
                </p>
            </div>
            <div class="bg-white border rounded p-4">
                <p class="text-sm text-gray-500">{{ __('Labor Ratio') }}</p>
                <p class="text-3xl font-bold text-gray-700">{{ number_format(($kpis['labor_ratio'] ?? 0) * 100, 1) }}%</p>
            </div>
            <div class="bg-white border rounded p-4">
                <p class="text-sm text-gray-500">{{ __('Material Ratio') }}</p>
                <p class="text-3xl font-bold text-gray-700">{{ number_format(($kpis['material_ratio'] ?? 0) * 100, 1) }}%</p>
            </div>
            <div class="bg-white border rounded p-4">
                <p class="text-sm text-gray-500">{{ __('Revenue / Job') }}</p>
                <p class="text-2xl font-bold text-blue-700">
                    {{ $kpis['revenue_per_job'] !== null ? number_format($kpis['revenue_per_job'], 2) : '—' }}
                </p>
            </div>
            <div class="bg-white border rounded p-4">
                <p class="text-sm text-gray-500">{{ __('Revenue / Team') }}</p>
                <p class="text-2xl font-bold text-blue-700">
                    {{ $kpis['revenue_per_team'] !== null ? number_format($kpis['revenue_per_team'], 2) : '—' }}
                </p>
            </div>
            <div class="bg-white border rounded p-4">
                <p class="text-sm text-gray-500">{{ __('Cost / Site') }}</p>
                <p class="text-2xl font-bold text-orange-600">
                    {{ $kpis['cost_per_site'] !== null ? number_format($kpis['cost_per_site'], 2) : '—' }}
                </p>
            </div>
            <div class="bg-white border rounded p-4">
                <p class="text-sm text-gray-500">{{ __('Avg Job Profit') }}</p>
                <p class="text-2xl font-bold {{ ($kpis['avg_job_profit'] ?? 0) >= 0 ? 'text-green-700' : 'text-red-600' }}">
                    {{ $kpis['avg_job_profit'] !== null ? number_format($kpis['avg_job_profit'], 2) : '—' }}
                </p>
            </div>
            <div class="bg-white border rounded p-4">
                <p class="text-sm text-gray-500">{{ __('Cash Buffer Days') }}</p>
                <p class="text-2xl font-bold {{ ($kpis['cash_buffer_days'] ?? 0) >= 30 ? 'text-green-700' : 'text-red-600' }}">
                    {{ $kpis['cash_buffer_days'] ?? '—' }}
                </p>
            </div>
        </div>

        <div class="bg-white border rounded divide-y">
            <div class="px-4 py-3 flex justify-between">
                <span class="text-gray-600">{{ __('Total Revenue') }}</span>
                <span class="font-semibold text-green-700">{{ number_format($kpis['total_revenue'], 2) }}</span>
            </div>
            <div class="px-4 py-3 flex justify-between">
                <span class="text-gray-600">{{ __('Total Cost') }}</span>
                <span class="font-semibold text-red-600">{{ number_format($kpis['total_cost'], 2) }}</span>
            </div>
            <div class="px-4 py-3 flex justify-between">
                <span class="text-gray-600">{{ __('Operating Expenses') }}</span>
                <span class="font-semibold text-orange-600">{{ number_format($kpis['total_expenses'], 2) }}</span>
            </div>
            <div class="px-4 py-3 flex justify-between bg-gray-50">
                <span class="font-medium">{{ __('Gross Margin') }}</span>
                <span class="font-bold {{ $kpis['gross_margin'] >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ number_format($kpis['gross_margin'], 2) }}</span>
            </div>
            <div class="px-4 py-3 flex justify-between bg-blue-50">
                <span class="font-bold">{{ __('Net Margin') }}</span>
                <span class="font-bold {{ $kpis['net_margin'] >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ number_format($kpis['net_margin'], 2) }}</span>
            </div>
        </div>
    </div>
@endsection

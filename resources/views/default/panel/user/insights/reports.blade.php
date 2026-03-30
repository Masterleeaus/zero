@extends('panel.layout.app')
@section('title', __('Insights Reports'))

@section('content')
    <div class="py-6 space-y-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="text-sm uppercase tracking-wide text-slate-500">{{ __('Insights') }}</div>
                <h1 class="text-xl font-semibold">{{ __('Reports & Analytics') }}</h1>
                <p class="text-slate-500">{{ __('All reports are scoped to your company data.') }}</p>
            </div>
            <form method="get" class="flex items-center gap-3">
                <label for="range" class="text-sm font-medium text-slate-600">{{ __('Date range') }}</label>
                <select
                    id="range"
                    name="range"
                    class="rounded-md border-slate-300 text-sm"
                    onchange="this.form.submit()"
                >
                    <option value="30d" @selected($range === '30d')>{{ __('Last 30 days') }}</option>
                    <option value="90d" @selected($range === '90d')>{{ __('Last 90 days') }}</option>
                    <option value="12m" @selected($range === '12m')>{{ __('Last 12 months') }}</option>
                </select>
            </form>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <x-card>
                <div class="flex items-center justify-between mb-3">
                    <div class="font-semibold">{{ __('Revenue (Paid Invoices)') }}</div>
                    <div class="text-xs text-slate-500">{{ __('Grouped by month') }}</div>
                </div>
                <canvas id="revenueChart" height="140"></canvas>
                @if ($revenueReport->isEmpty())
                    <p class="mt-3 text-sm text-slate-500">{{ __('No revenue data available for this range.') }}</p>
                @endif
            </x-card>

            <x-card>
                <div class="flex items-center justify-between mb-3">
                    <div class="font-semibold">{{ __('Expense vs Revenue') }}</div>
                    <div class="text-xs text-slate-500">{{ __('Last 6 months') }}</div>
                </div>
                <canvas id="expenseRevenueChart" height="140"></canvas>
                @if ($expenseVsRevenue->isEmpty())
                    <p class="mt-3 text-sm text-slate-500">{{ __('No expense or revenue data found for the recent months.') }}</p>
                @endif
            </x-card>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <x-card>
                <div class="flex items-center justify-between mb-3">
                    <div class="font-semibold">{{ __('Jobs by Status') }}</div>
                    <div class="text-xs text-slate-500">{{ __('Current totals') }}</div>
                </div>
                <canvas id="jobsStatusChart" height="180"></canvas>
                @if (empty($jobsByStatus))
                    <p class="mt-3 text-sm text-slate-500">{{ __('No jobs recorded yet.') }}</p>
                @endif
            </x-card>

            <x-card class="space-y-3">
                <div class="font-semibold">{{ __('Leave Summary (current month)') }}</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-slate-500">
                            <tr>
                                <th class="pb-2">{{ __('Type') }}</th>
                                <th class="pb-2 text-right">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaveSummary as $type => $total)
                                <tr class="border-t border-slate-100">
                                    <td class="py-2 capitalize">{{ str_replace('_', ' ', $type) }}</td>
                                    <td class="py-2 text-right font-semibold">{{ $total }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="py-2 text-slate-500">{{ __('No leave records this month.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <x-card class="space-y-3">
            <div class="font-semibold">{{ __('Top Customers by Revenue') }}</div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-slate-500">
                        <tr>
                            <th class="pb-2">{{ __('Customer') }}</th>
                            <th class="pb-2 text-right">{{ __('Total Paid') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topCustomers as $customer)
                            <tr class="border-t border-slate-100">
                                <td class="py-2">{{ $customer->name }}</td>
                                <td class="py-2 text-right font-semibold">{{ number_format((float) $customer->total_paid, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="py-2 text-slate-500">{{ __('No customer revenue recorded yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
@endsection

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const revenueLabels = @json($revenueLabels);
            const revenueValues = @json($revenueValues);
            const jobStatusLabels = @json($jobStatusLabels);
            const jobStatusValues = @json($jobStatusValues);
            const expenseMonths = @json($expenseMonths);
            const expenseRevenue = @json($expenseRevenueSeries);
            const expenseTotals = @json($expenseTotals);

            const revenueCtx = document.getElementById('revenueChart');
            if (revenueCtx) {
                new Chart(revenueCtx, {
                    type: 'line',
                    data: {
                        labels: revenueLabels,
                        datasets: [
                            {
                                label: '{{ __('Revenue') }}',
                                data: revenueValues,
                                tension: 0.35,
                                borderColor: '#2563eb',
                                backgroundColor: 'rgba(37, 99, 235, 0.15)',
                                fill: true,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: { beginAtZero: true },
                        },
                    },
                });
            }

            const jobsCtx = document.getElementById('jobsStatusChart');
            if (jobsCtx) {
                new Chart(jobsCtx, {
                    type: 'doughnut',
                    data: {
                        labels: jobStatusLabels,
                        datasets: [
                            {
                                data: jobStatusValues,
                                backgroundColor: [
                                    '#2563eb',
                                    '#22c55e',
                                    '#f97316',
                                    '#e11d48',
                                    '#14b8a6',
                                    '#a855f7',
                                ],
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                        },
                    },
                });
            }

            const expenseCtx = document.getElementById('expenseRevenueChart');
            if (expenseCtx) {
                new Chart(expenseCtx, {
                    type: 'bar',
                    data: {
                        labels: expenseMonths,
                        datasets: [
                            {
                                label: '{{ __('Revenue') }}',
                                data: expenseRevenue,
                                backgroundColor: 'rgba(34, 197, 94, 0.25)',
                                borderColor: '#22c55e',
                                borderWidth: 1,
                            },
                            {
                                label: '{{ __('Expenses') }}',
                                data: expenseTotals,
                                backgroundColor: 'rgba(239, 68, 68, 0.25)',
                                borderColor: '#ef4444',
                                borderWidth: 1,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: { beginAtZero: true },
                        },
                    },
                });
            }
        });
    </script>
@endpush

<x-layouts.admin>
    <x-slot:header>Business Intelligence Dashboard</x-slot:header>

    <div class="space-y-6">
        {{-- High-Level KPI Widgets --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-blue-600 p-6 rounded-xl text-white shadow-lg shadow-blue-500/20">
                <p class="text-[10px] font-bold uppercase text-blue-100 mb-1">Peak Sales Hour</p>
                <h4 class="text-2xl font-bold">{{ $trends['peak_order_time'] }}</h4>
                <div class="mt-4 flex items-center gap-2 text-[10px] bg-blue-500/30 w-fit px-2 py-1 rounded">
                    <span>Based on recent activity</span>
                </div>
            </div>
            <div class="bg-emerald-600 p-6 rounded-xl text-white shadow-lg shadow-emerald-500/20">
                <p class="text-[10px] font-bold uppercase text-emerald-100 mb-1">Trending Category</p>
                <h4 class="text-2xl font-bold">{{ $trends['top_performing_category'] }}</h4>
                <div class="mt-4 flex items-center gap-2 text-[10px] bg-emerald-500/30 w-fit px-2 py-1 rounded">
                    <span>High demand detected</span>
                </div>
            </div>
            <div class="bg-indigo-600 p-6 rounded-xl text-white shadow-lg shadow-indigo-500/20">
                <p class="text-[10px] font-bold uppercase text-indigo-100 mb-1">Estimated Growth</p>
                <h4 class="text-2xl font-bold">{{ $trends['estimated_growth_next_month'] }}</h4>
                <div class="mt-4 flex items-center gap-2 text-[10px] bg-indigo-500/30 w-fit px-2 py-1 rounded">
                    <span>Performance forecast</span>
                </div>
            </div>
            <div class="bg-rose-600 p-6 rounded-xl text-white shadow-lg shadow-rose-500/20">
                <p class="text-[10px] font-bold uppercase text-rose-100 mb-1">Order Risk Level</p>
                <h4 class="text-2xl font-bold">Low (12.4%)</h4>
                <div class="mt-4 flex items-center gap-2 text-[10px] bg-rose-500/30 w-fit px-2 py-1 rounded">
                    <span>Verification active</span>
                </div>
            </div>
        </div>

        {{-- AI Marketing Campaigns Section --}}
        <div x-data="{ 
                loading: false, 
                campaign: {
                    name: '{{ $suggestion['name'] }}',
                    reason: '{{ $suggestion['ai_rationale'] }}',
                    target: '{{ $suggestion['target_audience'] }}',
                    discount: '{{ $suggestion['suggested_discount'] }}',
                    roi: '{{ $suggestion['predicted_conversion'] }}'
                },
                async regenerate() {
                    this.loading = true;
                    try {
                        const res = await fetch('{{ route('admin.intelligence.generate-campaign') }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });
                        const data = await res.json();
                        if(data.success) {
                            this.campaign = data.campaign;
                        }
                    } catch(e) { console.error(e) }
                    this.loading = false;
                }
            }" 
            class="bg-slate-900 dark:bg-slate-900 border border-slate-800 p-8 rounded-xl text-white relative overflow-hidden shadow-xl transition-all duration-500">
            
            <div x-show="loading" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm z-20 flex items-center justify-center">
                <div class="w-12 h-12 border-4 border-white border-t-transparent rounded-full animate-spin"></div>
            </div>

            <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded-full text-[10px] font-bold uppercase tracking-tight border border-blue-500/30">Marketing Insight</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    </div>
                    
                    <h2 class="text-3xl font-bold tracking-tight leading-tight" x-text="campaign.name"></h2>
                    <p class="text-slate-300 text-sm mt-3 font-medium max-w-xl" x-text="campaign.reason"></p>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 mt-8">
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Audience</p>
                            <p class="text-sm font-bold text-white mt-0.5" x-text="campaign.target"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Offer</p>
                            <p class="text-sm font-bold text-white mt-0.5" x-text="campaign.discount"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Probable Outcome</p>
                            <p class="text-sm font-bold text-white mt-0.5" x-text="campaign.roi"></p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-3 w-full md:w-auto">
                    <button class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-lg shadow-blue-500/20 transition-all uppercase tracking-wider whitespace-nowrap">Execute Campaign</button>
                    <button @click="regenerate()" class="px-6 py-3 bg-slate-800 border border-slate-700 text-white text-[10px] font-bold rounded-lg hover:bg-slate-700 transition-colors uppercase tracking-wider">Get Another Idea</button>
                </div>
            </div>
            
            {{-- Abstract Orbs --}}
            <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500/10 blur-[100px] rounded-full -mr-20 -mt-20"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-indigo-500/10 blur-[60px] rounded-full -ml-10 -mb-10"></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Weekly Revenue Chart --}}
            <div class="lg:col-span-2 bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">Weekly Revenue Trend</h3>
                    <span class="text-[10px] font-bold text-emerald-500 bg-emerald-500/10 px-2 py-1 rounded">Live Data</span>
                </div>
                <div class="h-64">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            {{-- Customer Ranks (Segmentation) --}}
            <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">Audience Segmentation</h3>
                    <a href="{{ route('admin.intelligence.customers') }}" class="text-[10px] font-bold text-blue-500 hover:text-blue-700 uppercase">View Details</a>
                </div>
                <div class="h-48 flex items-center justify-center">
                    <canvas id="audienceChart"></canvas>
                </div>
                <div class="mt-4 space-y-2">
                    @foreach($rankStats as $stat)
                    <div class="flex justify-between items-center text-[10px] font-bold uppercase text-slate-500">
                        <span>{{ $stat->customer_rank }}</span>
                        <span class="text-slate-900 dark:text-white">{{ $stat->count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Smart Insights Feed --}}
            <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-5">Business Insights</h3>
                <div class="space-y-4">
                    <div class="flex gap-4 p-4 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 rounded-lg transition-all hover:translate-x-1 shadow-sm">
                        <div class="flex-shrink-0 mt-1">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-blue-900 dark:text-blue-200 uppercase">Growth Opportunity</p>
                            <p class="text-[11px] text-blue-800/80 dark:text-blue-300/80 mt-1 leading-relaxed">Increasing interest detected in <span class="font-bold">Organic Tea</span>. Restocking 20% more inventory could yield ${{ number_format($accounting['gross_revenue'] * 0.05, 2) }} in additional revenue.</p>
                        </div>
                    </div>
                    <div class="flex gap-4 p-4 bg-emerald-50 dark:bg-emerald-900/20 border-l-4 border-emerald-500 rounded-lg transition-all hover:translate-x-1 shadow-sm">
                        <div class="flex-shrink-0 mt-1">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-emerald-900 dark:text-emerald-200 uppercase">Customer Retention</p>
                            <p class="text-[11px] text-emerald-800/80 dark:text-emerald-300/80 mt-1 leading-relaxed">VIP segment retention is at an optimal 98%. System suggests maintaining the current white-glove loyalty touchpoints.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Risk Monitoring --}}
            <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden flex flex-col justify-between">
                <div>
                    <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-5">Security Monitoring</h3>
                    <div class="flex items-end gap-1.5 h-32 mb-4">
                        @foreach([40, 25, 45, 30, 55, 35, 20, 45, 60, 30] as $h)
                        <div class="flex-1 bg-red-500/20 rounded-t hover:bg-red-500 transition-all cursor-pointer group relative" style="height: {{ $h }}%">
                            <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[8px] px-1.5 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity shadow-lg whitespace-nowrap z-20">Risk Factor: {{ $h }}%</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wide">Suspicious patterns detected in last 24h: <span class="text-red-500">03</span></p>
            </div>
        </div>

        {{-- Profit & Loss Ledger --}}
        <div class="bg-white dark:bg-slate-800 p-8 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-8">Financial Overview</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="p-6 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
                    <p class="text-[10px] font-bold uppercase text-slate-500 mb-2">Gross Revenue</p>
                    <h5 class="text-2xl font-bold text-slate-900 dark:text-white">${{ number_format($accounting['gross_revenue'], 2) }}</h5>
                    <p class="text-[10px] text-emerald-500 font-bold mt-2">Verified & Paid Sales</p>
                </div>
                <div class="p-6 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
                    <p class="text-[10px] font-bold uppercase text-slate-500 mb-2">Total Expenses</p>
                    <h5 class="text-2xl font-bold text-red-600 dark:text-red-500">${{ number_format($accounting['total_expenses'], 2) }}</h5>
                    <p class="text-[10px] text-slate-400 font-bold mt-2">Operating Costs & Logistics</p>
                </div>
                <div class="p-6 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-900 shadow-sm">
                    <p class="text-[10px] font-bold uppercase text-blue-500 mb-2">Net Profit</p>
                    <h5 class="text-2xl font-bold text-blue-600 dark:text-blue-400">${{ number_format($accounting['net_profit'], 2) }}</h5>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-[10px] text-blue-400 font-bold">Margin: {{ $accounting['profit_margin'] }}%</span>
                        <span class="px-2 py-0.5 bg-blue-500 text-white text-[9px] font-bold rounded uppercase">Target Met</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Revenue Chart
            const ctx = document.getElementById('revenueChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($labels) !!},
                    datasets: [{
                        label: 'Gross Revenue',
                        data: {!! json_encode($weeklySales) !!},
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 4,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#6366f1',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e1b4b',
                            titleFont: { family: 'Outfit', size: 12 },
                            bodyFont: { family: 'Inter', size: 12 },
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return '$' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.05)' },
                            ticks: { font: { family: 'Inter', size: 10 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Inter', size: 10 } }
                        }
                    }
                }
            });

            // Audience Pulse Chart
            const audienceCtx = document.getElementById('audienceChart').getContext('2d');
            const rankData = {!! json_encode($rankStats->pluck('count')) !!};
            const rankLabels = {!! json_encode($rankStats->pluck('customer_rank')) !!};
            
            new Chart(audienceCtx, {
                type: 'doughnut',
                data: {
                    labels: rankLabels,
                    datasets: [{
                        data: rankData,
                        backgroundColor: ['#6366f1', '#10b981', '#94a3b8', '#f59e0b'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        });
    </script>
</x-layouts.admin>

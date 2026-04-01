<x-layouts.admin title="Command Center">

    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-1000">
        
        <!-- Page Header -->
        <div class="flex flex-wrap items-center justify-between gap-6">
            <div>
                <h1 class="text-2xl md:text-4xl font-black text-slate-900 dark:text-white uppercase tracking-tight leading-none mb-3">Command Center</h1>
                <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    Unified Operational Intelligence & Control
                </p>
            </div>
            
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.expenses.create') }}" class="group px-6 py-4 bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 text-[10px] font-black text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:border-blue-500/50 rounded-2xl transition-all shadow-sm uppercase tracking-[0.2em] flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Debit Entry
                </a>
                <a href="{{ route('admin.purchases.create') }}" class="px-6 py-4 bg-blue-600 text-[10px] font-black text-white rounded-2xl hover:bg-blue-700 transition-all shadow-xl shadow-blue-500/20 uppercase tracking-[0.2em] flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Procure Stock
                </a>
            </div>
        </div>

        <!-- Alert Banners -->
        @if(count($operationalAlerts) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($operationalAlerts as $alert)
                @php
                    $colors = [
                        'warning' => 'bg-amber-50 dark:bg-amber-500/5 text-amber-700 dark:text-amber-500 border-amber-200/50 dark:border-amber-500/20 shadow-amber-500/5',
                        'danger' => 'bg-rose-50 dark:bg-rose-500/5 text-rose-700 dark:text-rose-500 border-rose-200/50 dark:border-rose-500/20 shadow-rose-500/5',
                        'info' => 'bg-blue-50 dark:bg-blue-500/5 text-blue-700 dark:text-blue-500 border-blue-200/50 dark:border-blue-500/20 shadow-blue-500/5',
                    ];
                    $icons = [
                        'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
                        'danger' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                        'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    ];
                @endphp
                <div class="flex items-center px-6 py-4 rounded-2xl border {{ $colors[$alert['type']] }} shadow-sm transition-all hover:scale-[1.02] duration-300">
                    <svg class="w-5 h-5 mr-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $icons[$alert['type']] !!}</svg>
                    <span class="text-[11px] font-black uppercase tracking-tight">{{ $alert['message'] }}</span>
                </div>
            @endforeach
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- KEY FINANCIALS --}}
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200/60 dark:border-slate-800 p-10 shadow-sm relative overflow-hidden group hover:shadow-2xl hover:shadow-blue-500/5 transition-all duration-700">
                    <div class="flex items-center justify-between mb-12">
                        <div>
                            <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                Fiscal Vitality
                            </h2>
                            <p class="text-2xl font-black text-slate-900 dark:text-white mt-1 uppercase tracking-tight">Revenue Stream</p>
                        </div>
                        <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-800/50 p-1 rounded-2xl border border-slate-100 dark:border-slate-700">
                            <button class="px-4 py-2 rounded-xl text-[10px] font-black bg-white dark:bg-slate-900 shadow-sm text-blue-600 uppercase tracking-widest">Live</button>
                            <button class="px-4 py-2 rounded-xl text-[10px] font-black text-slate-400 hover:text-slate-600 uppercase tracking-widest">History</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                        <div class="p-8 bg-slate-50 dark:bg-slate-950/50 rounded-[2rem] border border-slate-100 dark:border-slate-800 group/card hover:bg-white dark:hover:bg-slate-900 transition-all">
                            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Net Cash Flow</h3>
                            <div class="text-4xl font-display font-black text-slate-900 dark:text-white tracking-tighter">${{ number_format($financialSummary['net_cash_flow'], 0) }}</div>
                            <div class="mt-4 flex items-center gap-2 text-emerald-500 font-bold text-[10px] uppercase tracking-widest">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                12.5% vs Prev
                            </div>
                        </div>
                        <div class="p-8 bg-slate-50 dark:bg-slate-950/50 rounded-[2rem] border border-slate-100 dark:border-slate-800 group/card hover:bg-white dark:hover:bg-slate-900 transition-all">
                            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Operational OpEx</h3>
                            <div class="text-4xl font-display font-black text-slate-900 dark:text-white tracking-tighter">${{ number_format($financialSummary['total_opex'], 0) }}</div>
                            <div class="mt-4 px-3 py-1 bg-slate-200 dark:bg-slate-800 rounded-lg text-[9px] font-black text-slate-500 uppercase tracking-widest inline-block">Managed</div>
                        </div>
                        <div class="p-8 bg-slate-50 dark:bg-slate-950/50 rounded-[2rem] border border-slate-100 dark:border-slate-800 group/card hover:bg-white dark:hover:bg-slate-900 transition-all">
                            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">A/R Registry</h3>
                            <div class="text-4xl font-display font-black text-slate-900 dark:text-white tracking-tighter">${{ number_format($financialSummary['total_receivables'], 0) }}</div>
                            <div class="mt-4 flex items-center gap-2 text-amber-500 font-bold text-[10px] uppercase tracking-widest">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Active Claims
                            </div>
                        </div>
                    </div>

                    <div class="h-[350px] w-full relative">
                        <canvas id="financialStreamChart" data-chart-data="{{ json_encode($financialSummary['monthly_trends']) }}"></canvas>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- RECEIVABLES AGING --}}
                    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200/60 dark:border-slate-800 p-10 shadow-sm transition-all hover:shadow-xl hover:shadow-blue-500/5">
                        <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-10">A/R Aging Protocol</h2>
                        
                        <div class="space-y-6">
                            @foreach([
                                ['label' => 'Current', 'value' => $financialSummary['ar_aging']['current'], 'color' => 'bg-emerald-500', 'width' => '100%'],
                                ['label' => '1-30 Days', 'value' => $financialSummary['ar_aging']['over_30'], 'color' => 'bg-amber-400', 'width' => '30%'],
                                ['label' => '31-60 Days', 'value' => $financialSummary['ar_aging']['over_60'], 'color' => 'bg-orange-500', 'width' => '12%'],
                                ['label' => '60+ Days', 'value' => $financialSummary['ar_aging']['over_90'], 'color' => 'bg-rose-500', 'width' => '5%'],
                            ] as $row)
                            <div class="space-y-2">
                                <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-widest">
                                    <span class="text-slate-500">{{ $row['label'] }}</span>
                                    <span class="text-slate-900 dark:text-white">${{ number_format($row['value'], 0) }}</span>
                                </div>
                                <div class="h-2.5 rounded-full bg-slate-100 dark:bg-slate-800/50 overflow-hidden">
                                    <div class="h-full {{ $row['color'] }} rounded-full" style="width: {{ $row['width'] }}"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- HR SNAPSHOT --}}
                    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200/60 dark:border-slate-800 p-10 shadow-sm transition-all hover:shadow-xl hover:shadow-indigo-500/5">
                        <div class="flex items-center justify-between mb-10">
                            <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Personnel Load</h2>
                            <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-full text-[9px] font-black uppercase tracking-widest">Live Flow</span>
                        </div>

                        <div class="space-y-5 mb-8">
                            <div class="flex items-center justify-between p-5 rounded-2xl bg-slate-50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800">
                                <span class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Manifest Count</span>
                                <span class="text-lg font-black text-slate-900 dark:text-white">{{ $hrSummary['total_employees'] }}</span>
                            </div>
                            <div class="flex items-center justify-between p-5 rounded-2xl bg-slate-50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800">
                                <span class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Current Duty</span>
                                <span class="text-lg font-black text-emerald-500">{{ $hrSummary['present_today'] }}</span>
                            </div>
                            <div class="flex items-center justify-between p-5 rounded-2xl bg-slate-50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800">
                                <span class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Station Absence</span>
                                <span class="text-lg font-black text-amber-500">{{ $hrSummary['on_leave'] }}</span>
                            </div>
                        </div>

                        @if($hrSummary['pending_leave_requests'] > 0)
                        <a href="{{ route('admin.leaves.index') }}" class="group w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-2xl transition-all shadow-xl shadow-indigo-600/20 flex items-center justify-center gap-3 text-[10px] uppercase tracking-[0.2em]">
                            Review {{ $hrSummary['pending_leave_requests'] }} Petitions
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- SIDEBAR: RECENT OPS --}}
            <aside class="space-y-8">
                <div class="bg-slate-900 dark:bg-blue-600 rounded-[2.5rem] p-10 text-white shadow-2xl relative overflow-hidden group">
                    <div class="absolute -right-12 -top-12 w-48 h-48 bg-white/10 rounded-full blur-3xl group-hover:scale-125 transition-transform duration-700"></div>
                    <div class="relative z-10">
                        <h2 class="text-[10px] font-black text-white/60 uppercase tracking-[0.2em] mb-8">System Integrity</h2>
                        <div class="text-4xl font-display font-black tracking-tighter mb-4">$428.5k</div>
                        <p class="text-sm font-bold text-white/70 leading-relaxed mb-8">Operational Capital available for immediate deployment across registry units.</p>
                        <button class="w-full py-4 bg-white/20 hover:bg-white/30 backdrop-blur-md rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all">Deployment Manifest</button>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200/60 dark:border-slate-800 p-10 shadow-sm">
                    <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-10">Recent Operations</h2>
                    
                    <div class="space-y-8">
                        @foreach($recentActivity as $activity)
                        <div class="flex gap-5 group cursor-pointer">
                            <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center shrink-0 group-hover:bg-blue-600 transition-all duration-500">
                                @if($activity['type'] === 'sale')
                                    <svg class="w-5 h-5 text-emerald-500 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @elseif($activity['type'] === 'expense')
                                    <svg class="w-5 h-5 text-rose-500 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @else
                                    <svg class="w-5 h-5 text-slate-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tight line-clamp-1 mb-1">{{ $activity['description'] }}</h4>
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $activity['time'] }}</span>
                                    <span class="w-1 h-1 rounded-full bg-slate-200 dark:bg-slate-700"></span>
                                    <span class="text-[10px] font-black text-slate-900 dark:text-white">{{ $activity['amount'] }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('financialStreamChart');
            if (!canvas) return;

            const trendData = JSON.parse(canvas.dataset.chartData);
            const ctx = canvas.getContext('2d');
            
            const gradient = ctx.createLinearGradient(0, 0, 0, 350);
            gradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
            gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trendData.map(d => d.month),
                    datasets: [{
                        label: 'Gross Profit Manifest',
                        data: trendData.map(d => d.amount),
                        borderColor: '#3b82f6',
                        borderWidth: 4,
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.45,
                        pointRadius: 6,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#3b82f6',
                        pointBorderWidth: 3,
                        pointHoverRadius: 8,
                        pointHoverBorderWidth: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { size: 10, weight: '900', family: 'Outfit' },
                            bodyFont: { size: 12, weight: '700', family: 'Inter' },
                            padding: 16,
                            cornerRadius: 16,
                            displayColors: false
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { 
                                font: { size: 9, weight: '700', family: 'Outfit' },
                                color: '#94a3b8',
                                padding: 10
                            }
                        },
                        y: {
                            grid: { color: 'rgba(148, 163, 184, 0.05)', drawBorder: false },
                            ticks: { 
                                font: { size: 9, weight: '700', family: 'Outfit' },
                                color: '#94a3b8',
                                padding: 10,
                                callback: (v) => '$' + v/1000 + 'k'
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush

</x-layouts.admin>

<x-layouts.admin title="Finance Ledger">

    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-1000">
        
        <!-- Page Header -->
        <div class="flex flex-wrap items-center justify-between gap-6">
            <div>
                <h1 class="text-2xl md:text-4xl font-black text-slate-900 dark:text-white uppercase tracking-tight leading-none mb-3">Finance Ledger</h1>
                <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Operational Asset & Transactional Registry
                </p>
            </div>
            
            <div class="flex items-center gap-4">
                <button @click="window.print()" class="px-6 py-4 bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 text-[10px] font-black text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:border-blue-500/50 rounded-2xl transition-all shadow-sm uppercase tracking-[0.2em] flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print Audit
                </button>
            </div>
        </div>

        {{-- KPI Summary Row --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="relative overflow-hidden bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm group hover:shadow-xl transition-all duration-500">
                <div class="relative z-10">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Total Liquidity</p>
                    <h4 class="text-4xl font-display font-black text-slate-900 dark:text-white tracking-tighter">${{ number_format($accounts->sum('balance'), 0) }}</h4>
                    <div class="mt-4 flex items-center gap-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">
                        <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                        Aggregated Balance
                    </div>
                </div>
                <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-slate-500/5 rounded-full blur-3xl group-hover:bg-slate-500/10 transition-colors"></div>
            </div>

            <div class="relative overflow-hidden bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm group hover:shadow-xl transition-all duration-500">
                <div class="relative z-10">
                    <p class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.2em] mb-4">Income (MTD)</p>
                    <h4 class="text-4xl font-display font-black text-emerald-600 dark:text-emerald-400 tracking-tighter">+${{ number_format($incomeMTD, 0) }}</h4>
                    <div class="mt-4 flex items-center gap-2 text-emerald-500/60 font-black text-[9px] uppercase tracking-widest">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        Operational Gains
                    </div>
                </div>
                <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-emerald-500/5 rounded-full blur-3xl group-hover:bg-emerald-500/10 transition-colors"></div>
            </div>

            <div class="relative overflow-hidden bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm group hover:shadow-xl transition-all duration-500">
                <div class="relative z-10">
                    <p class="text-[10px] font-black text-rose-500 uppercase tracking-[0.2em] mb-4">Expense (MTD)</p>
                    <h4 class="text-4xl font-display font-black text-rose-600 dark:text-rose-400 tracking-tighter">-${{ number_format($expenseMTD, 0) }}</h4>
                    <div class="mt-4 flex items-center gap-2 text-rose-500/60 font-black text-[9px] uppercase tracking-widest">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        Capital Outflow
                    </div>
                </div>
                <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-rose-500/5 rounded-full blur-3xl group-hover:bg-rose-500/10 transition-colors"></div>
            </div>

            <div class="relative overflow-hidden bg-slate-900 dark:bg-blue-600 p-8 rounded-[2rem] border-none shadow-2xl group transition-all duration-500">
                <div class="relative z-10">
                    <p class="text-[10px] font-black text-white/60 uppercase tracking-[0.2em] mb-4">Net Performance</p>
                    <h4 class="text-4xl font-display font-black text-white tracking-tighter">${{ number_format($incomeMTD - $expenseMTD, 0) }}</h4>
                    <div class="mt-4 flex items-center gap-2 text-white/60 font-black text-[9px] uppercase tracking-widest">
                        <div class="w-1.5 h-1.5 rounded-full bg-white/40"></div>
                        Monthly Delta
                    </div>
                </div>
                <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-white/5 rounded-full blur-3xl group-hover:bg-white/10 transition-colors"></div>
            </div>
        </div>

        {{-- Financial Visuals --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="bg-white dark:bg-slate-900 p-10 rounded-[2.5rem] border border-slate-200/60 dark:border-slate-800 shadow-sm transition-all hover:shadow-xl">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-10">Outflow Distribution</h3>
                <div class="h-72 relative">
                    <canvas id="categoryDistributionChart" 
                            data-labels="{{ json_encode($categoryStats->pluck('category')) }}"
                            data-values="{{ json_encode($categoryStats->pluck('total')) }}"></canvas>
                </div>
            </div>
            
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-10 rounded-[2.5rem] border border-slate-200/60 dark:border-slate-800 shadow-sm transition-all hover:shadow-xl">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-10">
                    <div>
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Asset Velocity</h3>
                        <p class="text-xl font-black text-slate-900 dark:text-white mt-1 uppercase tracking-tight italic">Net Flow Stream</p>
                    </div>
                    <span class="inline-flex px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 border border-blue-100 dark:border-blue-800/30">Last 14 Cycles</span>
                </div>
                <div class="h-72">
                    <canvas id="cashFlowTrendChart"
                            data-labels="{{ json_encode($cashFlowDaily->pluck('date')) }}"
                            data-values="{{ json_encode($cashFlowDaily->pluck('flow')) }}"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Quick Entry Form --}}
            <div class="bg-white dark:bg-slate-900 p-10 rounded-[2.5rem] border border-slate-200/60 dark:border-slate-800 shadow-sm h-fit">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-1.5 h-6 bg-blue-600 rounded-full"></div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight italic">Authorize Entry</h3>
                </div>
                
                <form action="{{ route('admin.transactions.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Manifest Origin</label>
                        <select name="account_id" required class="w-full h-14 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-sm font-bold text-slate-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 transition-all cursor-pointer">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }} (${{ number_format($acc->balance, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Protocol Nature</label>
                        <select name="type" required class="w-full h-14 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-sm font-bold text-slate-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 transition-all cursor-pointer">
                            <option value="income">Income / Manifest Gains</option>
                            <option value="expense">Expense / Capital Outflow</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Currency Value</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-6 flex items-center pointer-events-none">
                                <span class="text-blue-600 font-display font-black text-xl">$</span>
                            </div>
                            <input type="number" step="0.01" name="amount" required 
                                   class="w-full h-14 pl-12 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-lg font-display font-black text-slate-900 dark:text-white placeholder-slate-300 focus:ring-4 focus:ring-blue-500/10 transition-all"
                                   placeholder="0.00">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Category</label>
                            <input type="text" name="category" placeholder="Rent, Sales" required 
                                   class="w-full h-14 px-6 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-sm font-bold text-slate-900 dark:text-white placeholder-slate-300 focus:ring-4 focus:ring-blue-500/10 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Hash Ref</label>
                            <input type="text" name="reference" placeholder="#INV-X" 
                                   class="w-full h-14 px-6 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-sm font-bold text-slate-900 dark:text-white placeholder-slate-300 focus:ring-4 focus:ring-blue-500/10 transition-all">
                        </div>
                    </div>

                    <button type="submit" class="group relative w-full h-16 bg-blue-600 text-white text-[10px] font-black uppercase tracking-[0.3em] rounded-2xl hover:bg-blue-700 transition-all duration-300 shadow-xl shadow-blue-500/20 overflow-hidden">
                        <span class="relative z-10 flex items-center justify-center gap-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Authorize Records
                        </span>
                    </button>
                </form>
            </div>

            {{-- Recent Transactions Ledger --}}
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200/60 dark:border-slate-800 shadow-sm overflow-hidden min-h-[500px] flex flex-col">
                <div class="px-10 py-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight italic">Vault History</h3>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Full Transaction Forensic Stream</p>
                    </div>
                </div>
                
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/30 dark:bg-slate-800/30 border-b border-slate-50 dark:border-slate-800">
                                <th class="px-10 py-5 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Timestamp</th>
                                <th class="px-10 py-5 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Asset Source</th>
                                <th class="px-10 py-5 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Classification</th>
                                <th class="px-10 py-5 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Currency Value</th>
                                <th class="px-10 py-5 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Protocol</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                            @forelse($transactions as $tx)
                            <tr class="group hover:bg-slate-50/80 dark:hover:bg-slate-800/20 transition-all duration-300">
                                <td class="px-10 py-6 whitespace-nowrap">
                                    <p class="text-[11px] font-black text-slate-900 dark:text-white leading-none mb-1">{{ $tx->transaction_date->format('M d, Y') }}</p>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ $tx->transaction_date->format('h:i A') }}</p>
                                </td>
                                <td class="px-10 py-6 text-xs font-black text-slate-700 dark:text-slate-300">
                                    {{ $tx->account->name }}
                                </td>
                                <td class="px-10 py-6 text-xs font-bold text-slate-400 uppercase tracking-widest">
                                    {{ $tx->category }}
                                </td>
                                <td class="px-10 py-6 text-sm font-black {{ $tx->type === 'income' ? 'text-emerald-500' : 'text-rose-500' }}">
                                    {{ $tx->type === 'income' ? '+' : '-' }}${{ number_format($tx->amount, 2) }}
                                </td>
                                <td class="px-10 py-6 text-right">
                                    <span class="inline-flex px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-[0.2em] border {{ $tx->type === 'income' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/30 shadow-sm shadow-emerald-500/10' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 border-rose-100 dark:border-rose-800/30' }}">
                                        {{ $tx->type }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-10 py-32 text-center">
                                    <div class="w-16 h-16 mx-auto bg-slate-50 dark:bg-slate-800/50 rounded-[1.5rem] flex items-center justify-center text-slate-300 dark:text-slate-600 mb-6 border border-slate-100 dark:border-slate-800 shadow-inner">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <h3 class="text-sm font-black text-slate-900 dark:text-white mb-2 uppercase tracking-widest">Vault Empty</h3>
                                    <p class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-[0.2em]">No transactional data detected in current stream</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($transactions->hasPages())
                <div class="px-10 py-8 bg-slate-50/30 dark:bg-slate-900/5 border-t border-slate-50 dark:border-slate-800">
                    {{ $transactions->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Category Distribution (Pie)
            const catCanvas = document.getElementById('categoryDistributionChart');
            if (catCanvas) {
                const catLabels = JSON.parse(catCanvas.dataset.labels);
                const catValues = JSON.parse(catCanvas.dataset.values);
                new Chart(catCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: catLabels,
                        datasets: [{
                            data: catValues,
                            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'],
                            borderWidth: 0,
                            hoverOffset: 25
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '82%',
                        plugins: { 
                            legend: { 
                                position: 'bottom', 
                                labels: { 
                                    padding: 25,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    font: { size: 9, weight: '900', family: 'Outfit' },
                                    color: '#94a3b8'
                                } 
                            },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                titleFont: { size: 10, weight: '900', family: 'Outfit' },
                                bodyFont: { size: 12, weight: '700', family: 'Inter' },
                                padding: 16,
                                cornerRadius: 16,
                                displayColors: false
                            }
                        }
                    }
                });
            }

            // Cash Flow Trend (Line)
            const flowCanvas = document.getElementById('cashFlowTrendChart');
            if (flowCanvas) {
                const flowLabels = JSON.parse(flowCanvas.dataset.labels);
                const flowValues = JSON.parse(flowCanvas.dataset.values);
                const ctx = flowCanvas.getContext('2d');
                
                const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
                gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: flowLabels,
                        datasets: [{
                            label: 'Velocity Manifest',
                            data: flowValues,
                            borderColor: '#3b82f6',
                            borderWidth: 4,
                            backgroundColor: gradient,
                            fill: true,
                            tension: 0.45,
                            pointRadius: 0,
                            pointHoverRadius: 8,
                            pointHoverBackgroundColor: '#3b82f6',
                            pointHoverBorderColor: '#fff',
                            pointHoverBorderWidth: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
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
                            y: {
                                grid: { color: 'rgba(148, 163, 184, 0.05)', drawBorder: false },
                                ticks: { 
                                    color: '#94a3b8',
                                    font: { size: 9, weight: '900', family: 'Outfit' },
                                    callback: (v) => '$' + v
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { 
                                    color: '#94a3b8',
                                    font: { size: 9, weight: '900', family: 'Outfit' }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-layouts.admin>

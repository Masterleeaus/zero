<x-layouts.admin>
    <x-slot:header>Order Fulfillment</x-slot:header>

    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        {{-- Order Analytics --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Status Breakdown (Polar Area) --}}
            <div class="relative overflow-hidden bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm group hover:shadow-xl transition-all duration-500">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-8">Stage Analysis</h3>
                <div class="h-64 relative">
                    <canvas id="orderStatusChart"></canvas>
                </div>
            </div>

            {{-- Order Volume Trend (Streaming Area) --}}
            <div class="lg:col-span-2 relative overflow-hidden bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm group hover:shadow-xl transition-all duration-500">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
                    <div>
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] leading-none mb-1">Success Trend</h3>
                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">14-Day Performance</p>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-1 bg-blue-50 dark:bg-blue-900/20 rounded-full border border-blue-100 dark:border-blue-800/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                        <span class="text-[9px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest leading-none">Live Flow</span>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="orderTrendChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Filters & Actions --}}
        <div class="flex flex-wrap items-center justify-between gap-6">
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.orders.index') }}" 
                   class="px-6 py-2.5 text-[10px] font-black rounded-full transition-all uppercase tracking-[0.2em] {{ !request('status') ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-lg shadow-slate-200 dark:shadow-none' : 'bg-white dark:bg-slate-900 text-slate-400 dark:text-slate-500 border border-slate-200 dark:border-slate-800 hover:border-blue-500 hover:text-blue-600' }}">
                    Full Ledger
                </a>
                @foreach(['pending', 'paid', 'shipped', 'delivered', 'cancelled'] as $status)
                <a href="{{ route('admin.orders.index', ['status' => $status]) }}" 
                   class="px-6 py-2.5 text-[10px] font-black rounded-full transition-all uppercase tracking-[0.2em] {{ request('status') === $status ? 'bg-blue-600 text-white shadow-lg shadow-blue-200 dark:shadow-none' : 'bg-white dark:bg-slate-900 text-slate-400 dark:text-slate-500 border border-slate-200 dark:border-slate-800 hover:border-blue-500 hover:text-blue-600' }}">
                    {{ $status }}
                </a>
                @endforeach
            </div>
        </div>

        {{-- Orders Table --}}
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm overflow-hidden min-h-[500px] flex flex-col">
            <div class="overflow-x-auto flex-1 h-full min-h-0">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-50/30 dark:bg-slate-800/30 border-b border-slate-100 dark:border-slate-800 text-left">
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Identification</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Customer Entity</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Net Value</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Date</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                        @forelse($orders as $order)
                        <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all duration-300">
                            <td class="px-8 py-5 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center border border-slate-100 dark:border-slate-700 group-hover:bg-blue-50 group-hover:border-blue-100 dark:group-hover:bg-blue-900/20 dark:group-hover:border-blue-800/30 transition-colors">
                                        <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 11-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    </div>
                                    <span class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">#{{ $order->order_number }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-sm font-bold text-slate-900 dark:text-white leading-none mb-1">{{ $order->user->name }}</p>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ $order->user->email }}</p>
                            </td>
                            <td class="px-8 py-5">
                                <span class="text-sm font-black text-slate-900 dark:text-white">${{ number_format($order->total_price, 2) }}</span>
                            </td>
                            <td class="px-8 py-5">
                                @php
                                    $statusColor = match($order->status) {
                                        'pending' => 'bg-amber-50 text-amber-600 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/30',
                                        'paid' => 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/30',
                                        'shipped' => 'bg-blue-50 text-blue-600 border-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800/30',
                                        'delivered' => 'bg-indigo-50 text-indigo-600 border-indigo-100 dark:bg-indigo-900/20 dark:text-indigo-400 dark:border-indigo-800/30',
                                        'cancelled' => 'bg-rose-50 text-rose-600 border-rose-100 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800/30',
                                        default => 'bg-slate-50 text-slate-600 border-slate-100 dark:bg-slate-900/20 dark:text-slate-400 dark:border-slate-800/30'
                                    };
                                @endphp
                                <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border {{ $statusColor }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-sm font-medium text-slate-500 dark:text-slate-400">
                                {{ $order->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-8 py-5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.orders.show', $order) }}" 
                                       class="p-2.5 bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 dark:hover:text-blue-400 border border-slate-100 dark:border-slate-700 rounded-xl transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.orders.invoice', $order) }}" 
                                       class="p-2.5 bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 dark:hover:text-emerald-400 border border-slate-100 dark:border-slate-700 rounded-xl transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-8 py-24 text-center">
                                <div class="w-16 h-16 mx-auto bg-slate-50 dark:bg-slate-800/50 rounded-2xl flex items-center justify-center text-slate-300 dark:text-slate-600 mb-4 border border-slate-100 dark:border-slate-800 shadow-inner">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 11-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                </div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white mb-1 uppercase tracking-wider">Vault Empty</h3>
                                <p class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-widest">No orders found in this flow</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($orders->hasPages())
            <div class="px-8 py-6 bg-slate-50/50 dark:bg-slate-900/10 border-t border-slate-100 dark:border-slate-800 text-center">
                {{ $orders->links() }}
            </div>
            @endif
        </div>
    </div>
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Order Status (Polar Area)
            const statusCanvas = document.getElementById('orderStatusChart');
            if (statusCanvas) {
                const statusCtx = statusCanvas.getContext('2d');
                new Chart(statusCtx, {
                    type: 'polarArea',
                    data: {
                        labels: JSON.parse(statusCanvas.dataset.labels),
                        datasets: [{
                            data: JSON.parse(statusCanvas.dataset.values),
                            backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            r: { 
                                grid: { color: 'rgba(148, 163, 184, 0.1)' },
                                ticks: { display: false }
                            }
                        },
                        plugins: { 
                            legend: { 
                                position: 'bottom', 
                                labels: { 
                                    boxWidth: 8, 
                                    usePointStyle: true,
                                    font: { size: 9, weight: 'bold', family: 'Inter' },
                                    padding: 15,
                                    color: '#94a3b8'
                                } 
                            } 
                        }
                    }
                });
            }

            // Order Trend (Line)
            const trendCanvas = document.getElementById('orderTrendChart');
            if (trendCanvas) {
                const trendCtx = trendCanvas.getContext('2d');
                const trendGradient = trendCtx.createLinearGradient(0, 0, 0, 300);
                trendGradient.addColorStop(0, 'rgba(59, 130, 246, 0.15)');
                trendGradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: JSON.parse(trendCanvas.dataset.labels),
                        datasets: [{
                            label: 'Orders',
                            data: JSON.parse(trendCanvas.dataset.values),
                            borderColor: '#3b82f6',
                            backgroundColor: trendGradient,
                            fill: true,
                            tension: 0.4,
                            borderWidth: 4,
                            pointRadius: 0,
                            pointHoverRadius: 6,
                            pointHoverBackgroundColor: '#3b82f6',
                            pointHoverBorderColor: '#fff',
                            pointHoverBorderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                grid: { color: 'rgba(226, 232, 240, 0.4)', drawBorder: false },
                                ticks: { 
                                    color: '#94a3b8',
                                    font: { family: 'Inter', size: 10, weight: 'bold' },
                                    stepSize: 1
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { 
                                    color: '#94a3b8',
                                    font: { family: 'Inter', size: 10, weight: 'bold' }
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

<x-layouts.admin>
    <x-slot:header>Inventory Restock</x-slot:header>

    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        {{-- Top Bar --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div>
                <h2 class="text-3xl font-display font-black text-slate-900 dark:text-white tracking-tight uppercase">Restock Orders</h2>
                <p class="text-sm font-medium text-slate-500 mt-1.5 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    Manage supplier shipments and inventory inbound
                </p>
            </div>
            <a href="{{ route('admin.purchases.create') }}" class="group inline-flex items-center gap-2.5 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-2xl shadow-lg shadow-blue-500/25 transition-all hover:-translate-y-0.5 active:translate-y-0">
                <svg class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Create Restock Order
            </a>
        </div>

        {{-- Analytics --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="relative overflow-hidden bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm group hover:shadow-xl transition-all duration-500">
                <div class="relative z-10 text-center">
                    <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3">Total Restock Value</p>
                    <h4 class="text-4xl font-display font-black text-slate-900 dark:text-white">${{ number_format($totalSpend, 2) }}</h4>
                </div>
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-500/5 rounded-full blur-2xl group-hover:bg-blue-500/10 transition-colors"></div>
            </div>
            <div class="relative overflow-hidden bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm group hover:shadow-xl transition-all duration-500">
                <div class="relative z-10 text-center">
                    <p class="text-[10px] font-black text-amber-500 uppercase tracking-[0.2em] mb-3">Open Orders</p>
                    <h4 class="text-4xl font-display font-black text-slate-900 dark:text-white">{{ $pendingCount }}</h4>
                </div>
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-amber-500/5 rounded-full blur-2xl group-hover:bg-amber-500/10 transition-colors"></div>
            </div>
            <div class="relative overflow-hidden bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm group hover:shadow-xl transition-all duration-500">
                <div class="relative z-10 text-center">
                    <p class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.2em] mb-3">Successfully Received</p>
                    <h4 class="text-4xl font-display font-black text-slate-900 dark:text-white">{{ $receivedCount }}</h4>
                </div>
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl group-hover:bg-emerald-500/10 transition-colors"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 items-start">
            {{-- Restock Activity Chart --}}
            <div class="xl:col-span-1 bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">Restock History</h3>
                    <div class="flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800">
                        <span class="w-1 h-1 rounded-full bg-blue-500"></span>
                        <span class="text-[9px] font-bold text-blue-600 dark:text-blue-400 tracking-wider uppercase">14D Activity</span>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="procurementChart"></canvas>
                </div>
            </div>

            {{-- Main Table Area --}}
            <div class="xl:col-span-2 space-y-6">
                {{-- Filters --}}
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('admin.purchases.index') }}" 
                       class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-xl transition-all {{ !request('status') ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-lg shadow-slate-200 dark:shadow-none' : 'bg-white dark:bg-slate-900 text-slate-500 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                        All
                    </a>
                    @foreach(['pending', 'ordered', 'received', 'cancelled'] as $status)
                    <a href="{{ route('admin.purchases.index', ['status' => $status]) }}" 
                       class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-xl transition-all {{ request('status') === $status ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-lg shadow-slate-200 dark:shadow-none' : 'bg-white dark:bg-slate-900 text-slate-500 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                        {{ $status }}
                    </a>
                    @endforeach
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm overflow-hidden min-h-[500px] flex flex-col">
                    <div class="overflow-x-auto flex-1 h-full min-h-0">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 dark:bg-slate-800/30 border-b border-slate-100 dark:border-slate-800">
                                    <th class="text-left px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Order #</th>
                                    <th class="text-left px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Supplier</th>
                                    <th class="text-center px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Growth Status</th>
                                    <th class="text-right px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Value</th>
                                    <th class="text-right px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                                @forelse($purchases as $po)
                                <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all duration-300">
                                    <td class="px-8 py-5 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.purchases.show', $po) }}" class="text-sm font-bold text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ $po->purchase_number }}</a>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $po->created_at->format('M d, Y') }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $po->supplier_name }}</p>
                                        <p class="text-xs font-semibold text-slate-500">{{ $po->items_count }} items in shipment</p>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            @php
                                                $sc = match($po->status) {
                                                    'pending' => 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400 border-amber-100 dark:border-amber-800/30',
                                                    'ordered' => 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 border-blue-100 dark:border-blue-800/30',
                                                    'received' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/30',
                                                    'cancelled' => 'bg-rose-50 text-rose-600 dark:bg-rose-900/20 dark:text-rose-400 border-rose-100 dark:border-rose-800/30',
                                                    default => 'bg-slate-50 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border-slate-200 dark:border-slate-700',
                                                };
                                                $pc = match($po->payment_status) {
                                                    'paid' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/30',
                                                    'partial' => 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400 border-amber-100 dark:border-amber-800/30',
                                                    default => 'bg-slate-50 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border-slate-200 dark:border-slate-700',
                                                };
                                            @endphp
                                            <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border {{ $sc }}">{{ $po->status }}</span>
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[8px] font-black uppercase tracking-widest border {{ $pc }}">{{ $po->payment_status }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <p class="text-lg font-display font-black text-slate-900 dark:text-white leading-none">${{ number_format($po->total_amount, 2) }}</p>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('admin.purchases.show', $po) }}" class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-white dark:hover:bg-slate-700 border border-slate-100 dark:border-slate-800 transition-all shadow-sm" title="View Full Analysis">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                            @if($po->status === 'pending' || $po->status === 'ordered')
                                            <form method="POST" action="{{ route('admin.purchases.receive', $po) }}">
                                                @csrf
                                                <button type="submit" class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white transition-all shadow-sm border border-emerald-100 dark:border-emerald-800/30" title="Mark as Received">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-8 py-24 text-center">
                                        <div class="w-20 h-20 mx-auto bg-slate-50 dark:bg-slate-800/50 rounded-3xl flex items-center justify-center text-slate-300 dark:text-slate-600 mb-6 border border-slate-100 dark:border-slate-800 shadow-inner">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                        </div>
                                        <h3 class="text-xl font-display font-black text-slate-900 dark:text-white mb-2 uppercase tracking-tight">Zero Activity</h3>
                                        <p class="text-slate-500 dark:text-slate-400 text-sm max-w-xs mx-auto mb-8 font-medium leading-relaxed">No purchase orders have been processed yet. Start by sending a request to your supplier.</p>
                                        <a href="{{ route('admin.purchases.create') }}" class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 font-black text-xs uppercase tracking-[0.2em] hover:gap-4 transition-all">
                                            Initiate Restock <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($purchases->hasPages())
                    <div class="px-8 py-6 bg-slate-50/30 dark:bg-slate-900/10 border-t border-slate-100 dark:border-slate-800">
                        {{ $purchases->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('procurementChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($monthlySpend->pluck('date')) !!},
                    datasets: [{
                        label: 'Spend',
                        data: {!! json_encode($monthlySpend->pluck('total')) !!},
                        backgroundColor: '#2563eb', // blue-600
                        borderRadius: 4,
                        barThickness: 16
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.03)', drawBorder: false },
                            ticks: { callback: v => '$' + v, font: { family: 'Inter', size: 9, weight: 'bold' } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Inter', size: 9, weight: 'bold' } }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-layouts.admin>

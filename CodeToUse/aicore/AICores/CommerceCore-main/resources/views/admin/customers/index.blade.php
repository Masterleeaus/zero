<x-layouts.admin title="Customer Registry">

    <div class="space-y-10 animate-in fade-in slide-in-from-bottom-6 duration-1000">
        
        <!-- Page Header -->
        <div class="flex flex-wrap items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl md:text-5xl font-black text-slate-900 dark:text-white uppercase tracking-tighter leading-none mb-3 italic">Customer Registry</h1>
                <p class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.3em] flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                    Asset Management & Behavioral Analysis
                </p>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="relative group">
                    <input type="text" placeholder="Search customer hash..." 
                           class="w-72 h-14 pl-12 pr-6 bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none shadow-sm">
                    <svg class="w-4 h-4 text-slate-400 absolute left-5 top-5 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>
        </div>

        {{-- Alpha KPI Row --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-200/60 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:shadow-2xl transition-all duration-700">
                <div class="absolute -right-8 -top-8 w-32 h-32 bg-blue-500/5 rounded-full blur-3xl group-hover:bg-blue-500/10 transition-all"></div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-4">Total Population</p>
                <div class="flex items-end gap-3">
                    <h4 class="text-5xl font-display font-black text-slate-900 dark:text-white leading-none tracking-tighter">{{ number_format($totalCustomers) }}</h4>
                    <span class="text-[9px] font-black text-blue-500 uppercase tracking-widest mb-1 italic">Active Nodes</span>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-200/60 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:shadow-2xl transition-all duration-700">
                <div class="absolute -right-8 -top-8 w-32 h-32 bg-emerald-500/5 rounded-full blur-3xl group-hover:bg-emerald-500/10 transition-all"></div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-4">Ecosystem LTV</p>
                <div class="flex items-end gap-3">
                    <h4 class="text-5xl font-display font-black text-emerald-600 dark:text-emerald-400 leading-none tracking-tighter">${{ number_format($totalRevenue, 0) }}</h4>
                    <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest mb-1 italic">Accumulated</span>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-200/60 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:shadow-2xl transition-all duration-700">
                <div class="absolute -right-8 -top-8 w-32 h-32 bg-indigo-500/5 rounded-full blur-3xl group-hover:bg-indigo-500/10 transition-all"></div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-4">Mean Velocity</p>
                <div class="flex items-end gap-3">
                    <h4 class="text-5xl font-display font-black text-slate-900 dark:text-white leading-none tracking-tighter">${{ number_format($averageOrderValue, 0) }}</h4>
                    <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest mb-1 italic">Per Node</span>
                </div>
            </div>
        </div>

        {{-- Main Registry Table --}}
        <div class="bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-200/60 dark:border-slate-800 shadow-sm overflow-hidden min-h-[500px] flex flex-col transition-all hover:shadow-2xl">
            <div class="px-12 py-10 border-b border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-slate-950/20 flex justify-between items-center">
                <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight italic">Forensic Ledger</h3>
            </div>
            
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/30 dark:bg-slate-800/30 border-b border-slate-50 dark:border-white/5">
                            <th class="px-12 py-6 text-[9px] font-black text-slate-400 uppercase tracking-[0.3em]">Customer / Entity</th>
                            <th class="px-12 py-6 text-[9px] font-black text-slate-400 uppercase tracking-[0.3em]">Operational Grade</th>
                            <th class="px-12 py-6 text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] text-center">Frequency</th>
                            <th class="px-12 py-6 text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] text-right">LTV Value</th>
                            <th class="px-12 py-6 text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] text-right">Loyalty Hash</th>
                            <th class="px-12 py-6 text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] text-right">Control</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-white/5">
                        @forelse($customers as $customer)
                        <tr class="group hover:bg-blue-600/5 dark:hover:bg-blue-400/5 transition-all duration-300 cursor-pointer">
                            <td class="px-12 py-8">
                                <div class="flex items-center gap-6">
                                    <div class="w-14 h-14 rounded-2xl bg-white dark:bg-slate-800 flex items-center justify-center text-blue-600 dark:text-blue-400 font-black text-xl border border-slate-200/60 dark:border-slate-700 shadow-inner group-hover:bg-blue-600 group-hover:text-white transition-all">
                                        {{ substr($customer->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase leading-none mb-1.5">{{ $customer->name }}</h4>
                                        <p class="text-[10px] font-bold text-slate-400 italic lowercase">{{ $customer->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-12 py-8">
                                @php
                                    $rankColors = [
                                        'gold' => 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 border-amber-100 dark:border-amber-800/30 shadow-sm shadow-amber-500/10',
                                        'silver' => 'bg-slate-50 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border-slate-200/60 dark:border-slate-700',
                                        'bronze' => 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 border-blue-100 dark:border-blue-800/30'
                                    ];
                                    $rankColor = $rankColors[$customer->customer_rank] ?? $rankColors['bronze'];
                                @endphp
                                <span class="inline-flex px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-[0.2em] border {{ $rankColor }}">
                                    {{ $customer->customer_rank }} Node
                                </span>
                            </td>
                            <td class="px-12 py-8 text-center">
                                <span class="text-xs font-black text-slate-900 dark:text-white">{{ $customer->order_count }} Cycles</span>
                            </td>
                            <td class="px-12 py-8 text-right">
                                <span class="text-sm font-black text-slate-900 dark:text-white tracking-tighter">${{ number_format($customer->total_spent, 2) }}</span>
                            </td>
                            <td class="px-12 py-8 text-right text-[11px] font-black text-blue-600 dark:text-blue-400 italic">
                                {{ $customer->loyalty_points }} pts
                            </td>
                            <td class="px-12 py-8 text-right">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="inline-flex w-10 h-10 items-center justify-center bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700 rounded-xl text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:border-blue-500/50 transition-all shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-12 py-40 text-center">
                                <div class="w-20 h-20 mx-auto bg-slate-100 dark:bg-slate-800/50 rounded-[2rem] flex items-center justify-center text-slate-300 dark:text-slate-600 mb-8 border border-slate-100 dark:border-slate-800 shadow-inner">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2 uppercase tracking-widest leading-none">Registry Null</h3>
                                <p class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-[0.3em]">No customer nodes detected in current ecosystem</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($customers->hasPages())
            <div class="px-12 py-10 bg-slate-50/10 dark:bg-slate-900/5 border-t border-slate-50 dark:border-white/5">
                {{ $customers->links() }}
            </div>
            @endif
        </div>
    </div>
</x-layouts.admin>

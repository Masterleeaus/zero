    <x-slot:header>Customer Returns</x-slot:header>

    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-display font-black text-slate-900 dark:text-white tracking-tight uppercase">Product Returns</h2>
                <p class="text-sm font-medium text-slate-500 mt-1.5 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    Monitor and process customer refund requests
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="relative overflow-hidden bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm group hover:shadow-xl transition-all duration-500">
                <div class="relative z-10 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-amber-500 uppercase tracking-[0.2em] mb-3">Pending Verification</p>
                        <h4 class="text-4xl font-display font-black text-slate-900 dark:text-white leading-none">{{ $pendingReturns }}</h4>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600 dark:text-amber-400 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-amber-500/5 rounded-full blur-2xl group-hover:bg-amber-500/10 transition-colors"></div>
            </div>
            <div class="relative overflow-hidden bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm group hover:shadow-xl transition-all duration-500">
                <div class="relative z-10 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.2em] mb-3">Total Value Issued</p>
                        <h4 class="text-4xl font-display font-black text-slate-900 dark:text-white leading-none">${{ number_format($totalRefunded, 2) }}</h4>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                </div>
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl group-hover:bg-emerald-500/10 transition-colors"></div>
            </div>
        </div>

        <div class="space-y-6">
            {{-- Filters --}}
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.returns.index') }}" 
                   class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-xl transition-all {{ !request('status') ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-lg shadow-slate-200 dark:shadow-none' : 'bg-white dark:bg-slate-900 text-slate-500 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    All Requests
                </a>
                <a href="{{ route('admin.returns.index', ['status' => 'pending']) }}" 
                   class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-xl transition-all {{ request('status') == 'pending' ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/25' : 'bg-white dark:bg-slate-900 text-slate-500 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    Pending
                </a>
                <a href="{{ route('admin.returns.index', ['status' => 'refunded']) }}" 
                   class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-xl transition-all {{ request('status') == 'refunded' ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/25' : 'bg-white dark:bg-slate-900 text-slate-500 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    Refunded
                </a>
                <a href="{{ route('admin.returns.index', ['status' => 'rejected']) }}" 
                   class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-xl transition-all {{ request('status') == 'rejected' ? 'bg-rose-500 text-white shadow-lg shadow-rose-500/25' : 'bg-white dark:bg-slate-900 text-slate-500 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    Rejected
                </a>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm overflow-hidden min-h-[500px] flex flex-col">
                <div class="overflow-x-auto flex-1 h-full min-h-0">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-800/30 border-b border-slate-100 dark:border-slate-800">
                                <th class="text-left px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Return ID</th>
                                <th class="text-left px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Original Order</th>
                                <th class="text-left px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Reason</th>
                                <th class="text-right px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Refund Value</th>
                                <th class="text-center px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
                                <th class="text-right px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                            @forelse($returns as $return)
                            <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all duration-300">
                                <td class="px-8 py-5 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 flex items-center justify-center font-mono text-xs font-bold text-slate-400 group-hover:scale-110 transition-transform">
                                            RET
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 dark:text-white leading-none mb-1">{{ $return->return_number }}</p>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $return->created_at->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <a href="{{ route('admin.orders.show', $return->order_id) }}" class="inline-flex items-center gap-2 group/link">
                                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400 group-hover/link:underline transition-all decoration-2 underline-offset-4">#{{ $return->order->order_number }}</span>
                                        <svg class="w-3.5 h-3.5 text-slate-300 group-hover/link:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </td>
                                <td class="px-8 py-5">
                                    <p class="text-sm font-medium text-slate-600 dark:text-slate-300 truncate max-w-[200px]">{{ $return->reason }}</p>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <span class="text-lg font-display font-black text-slate-900 dark:text-white leading-none">${{ number_format($return->total_refund_amount, 2) }}</span>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    @php
                                        $sc = match($return->status) {
                                            'pending' => 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400 border-amber-100 dark:border-amber-800/30',
                                            'refunded' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/30',
                                            'rejected' => 'bg-rose-50 text-rose-600 dark:bg-rose-900/20 dark:text-rose-400 border-rose-100 dark:border-rose-800/30',
                                            default => 'bg-slate-50 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border-slate-200 dark:border-slate-700',
                                        };
                                    @endphp
                                    <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border {{ $sc }}">{{ $return->status }}</span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('admin.returns.show', $return) }}" class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-white dark:hover:bg-slate-700 border border-slate-100 dark:border-slate-800 transition-all shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-8 py-24 text-center">
                                    <div class="w-20 h-20 mx-auto bg-slate-50 dark:bg-slate-800/50 rounded-3xl flex items-center justify-center text-slate-300 dark:text-slate-600 mb-6 border border-slate-100 dark:border-slate-800 shadow-inner">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                                    </div>
                                    <h3 class="text-xl font-display font-black text-slate-900 dark:text-white mb-2 uppercase tracking-tight">Vault Empty</h3>
                                    <p class="text-slate-500 dark:text-slate-400 text-sm max-w-xs mx-auto mb-8 font-medium leading-relaxed">No return requests have been logged in the system yet.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($returns->hasPages())
                <div class="px-8 py-6 bg-slate-50/30 dark:bg-slate-900/10 border-t border-slate-100 dark:border-slate-800">
                    {{ $returns->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin>

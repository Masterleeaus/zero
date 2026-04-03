    <x-slot:header>Supply Partners</x-slot:header>

    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div>
                <h2 class="text-3xl font-display font-black text-slate-900 dark:text-white tracking-tight uppercase">Supply Chain</h2>
                <p class="text-sm font-medium text-slate-500 mt-1.5 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    Manage your global vendor network and procurement
                </p>
            </div>
            <a href="{{ route('admin.suppliers.create') }}" class="group relative inline-flex items-center gap-3 px-8 py-4 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-blue-600 dark:hover:bg-blue-500 hover:text-white transition-all duration-300 shadow-2xl shadow-slate-200 dark:shadow-none overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <svg class="relative z-10 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                <span class="relative z-10">Add Partner</span>
            </a>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="relative overflow-hidden bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm group hover:shadow-xl transition-all duration-500">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Total Network</p>
                <h4 class="text-4xl font-display font-black text-slate-900 dark:text-white leading-none">{{ $totalSuppliers }}</h4>
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-slate-500/5 rounded-full blur-2xl transition-colors"></div>
            </div>
            <div class="relative overflow-hidden bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm group hover:shadow-xl transition-all duration-500">
                <p class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.2em] mb-3">Active Status</p>
                <h4 class="text-4xl font-display font-black text-emerald-600 dark:text-emerald-400 leading-none">{{ $activeSuppliers }}</h4>
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl transition-colors"></div>
            </div>
            <div class="relative overflow-hidden bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm group hover:shadow-xl transition-all duration-500">
                <p class="text-[10px] font-black text-blue-500 uppercase tracking-[0.2em] mb-3">Total Procurement</p>
                <h4 class="text-4xl font-display font-black text-slate-900 dark:text-white leading-none">${{ number_format($totalSpend, 2) }}</h4>
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-500/5 rounded-full blur-2xl transition-colors"></div>
            </div>
        </div>

        {{-- Table Section --}}
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm overflow-hidden min-h-[500px] flex flex-col">
            <div class="overflow-x-auto flex-1 h-full min-h-0">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/30 border-b border-slate-100 dark:border-slate-800">
                            <th class="text-left px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Supplier Profile</th>
                            <th class="text-left px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Communication</th>
                            <th class="text-center px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Orders</th>
                            <th class="text-right px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Total Value</th>
                            <th class="text-center px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
                            <th class="text-right px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Management</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                        @forelse($suppliers as $supplier)
                        <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all duration-300">
                            <td class="px-8 py-5 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 flex items-center justify-center font-display font-black text-xs text-slate-400 group-hover:scale-110 transition-transform uppercase">
                                        {{ substr($supplier->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900 dark:text-white leading-none mb-1">{{ $supplier->name }}</p>
                                        @if($supplier->company)
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $supplier->company }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                @if($supplier->email)
                                    <p class="text-sm font-medium text-slate-600 dark:text-slate-300">{{ $supplier->email }}</p>
                                @endif
                                @if($supplier->phone)
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">{{ $supplier->phone }}</p>
                                @endif
                            </td>
                            <td class="px-8 py-5 text-center">
                                <span class="text-sm font-black text-slate-900 dark:text-white">{{ $supplier->purchases_count }}</span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <span class="text-lg font-display font-black text-slate-900 dark:text-white leading-none tracking-tight">${{ number_format($supplier->purchases_sum_total_amount ?? 0, 2) }}</span>
                            </td>
                            <td class="px-8 py-5 text-center">
                                <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border transition-all duration-300 {{ $supplier->status === 'active' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/30' : 'bg-slate-50 text-slate-500 dark:bg-slate-800 dark:text-slate-400 border-slate-200 dark:border-slate-700' }}">
                                    {{ $supplier->status }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-white dark:hover:bg-slate-700 border border-slate-100 dark:border-slate-800 transition-all shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.suppliers.destroy', $supplier) }}" onsubmit="return confirm('Archive this supply partner profile?')">
                                        @csrf @method('DELETE')
                                        <button class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-white dark:hover:bg-slate-700 border border-slate-100 dark:border-slate-800 transition-all shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-8 py-24 text-center">
                                <div class="w-20 h-20 mx-auto bg-slate-50 dark:bg-slate-800/50 rounded-3xl flex items-center justify-center text-slate-300 dark:text-slate-600 mb-6 border border-slate-100 dark:border-slate-800 shadow-inner">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                <h3 class="text-xl font-display font-black text-slate-900 dark:text-white mb-2 uppercase tracking-tight">Network Empty</h3>
                                <p class="text-slate-500 dark:text-slate-400 text-sm max-w-xs mx-auto mb-8 font-medium leading-relaxed">No supply partners have been registered. Add your first vendor to start procurement.</p>
                                <a href="{{ route('admin.suppliers.create') }}" class="inline-flex items-center gap-2 text-blue-600 font-bold hover:underline py-2 tracking-wide uppercase text-xs">Add First Partner →</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($suppliers->hasPages())
                <div class="px-8 py-6 bg-slate-50/30 dark:bg-slate-900/10 border-t border-slate-100 dark:border-slate-800">
                    {{ $suppliers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>

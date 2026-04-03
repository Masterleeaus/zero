<x-layouts.admin>
    <x-slot:header>Stock Movement</x-slot:header>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Stock Movement</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium italic">Track items moving across your business locations.</p>
            </div>
            <a href="{{ route('admin.inventory-transfers.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg shadow-sm transition-colors focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:focus:ring-offset-slate-900 uppercase tracking-widest">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                Move Stock
            </a>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="text-left px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] whitespace-nowrap">Date & Time</th>
                            <th class="text-left px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Item</th>
                            <th class="text-left px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Origin</th>
                            <th class="text-center px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Pathway</th>
                            <th class="text-left px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Destination</th>
                            <th class="text-right px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Amount</th>
                            <th class="text-right px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Condition</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        @forelse($transfers as $transfer)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ $transfer->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $transfer->product->name }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 text-xs font-semibold rounded-md border border-slate-200 dark:border-slate-700">
                                    {{ $transfer->fromBranch->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-slate-400">
                                <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 text-xs font-semibold rounded-md border border-blue-100 dark:border-blue-900/50">
                                    {{ $transfer->toBranch->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-blue-600 dark:text-blue-400 text-right">{{ $transfer->quantity }}</td>
                            <td class="px-6 py-4 text-right">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400 border border-green-200 dark:border-green-800/40">
                                    {{ $transfer->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="w-12 h-12 mx-auto bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center text-slate-400 mb-4 border border-slate-200 dark:border-slate-700 shadow-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                </div>
                                <h3 class="font-bold text-slate-900 dark:text-white mb-1">No transfers yet</h3>
                                <p class="text-slate-500 dark:text-slate-400 text-sm mb-4">You haven't initiated any inventory transfers between branches.</p>
                                <a href="{{ route('admin.inventory-transfers.create') }}" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-semibold text-sm transition-colors">Start a new transfer &rarr;</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($transfers->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                {{ $transfers->links() }}
            </div>
            @endif
        </div>
    </div>
</x-layouts.admin>

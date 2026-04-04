<x-layouts.admin>
    <x-slot:header>Financial Performance</x-slot:header>

    <div class="space-y-6">
        {{-- Report Filters --}}
        <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-wrap gap-4 items-center">
            <div class="flex-1">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Net Performance</h3>
                <p class="text-sm text-slate-500">Summary of earnings and costs for the selected period.</p>
            </div>
            <div class="flex gap-2">
                <button class="px-4 py-2 text-xs font-bold bg-slate-100 dark:bg-slate-700 rounded-lg text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors uppercase tracking-wider">Current Month</button>
                <button class="px-4 py-2 text-xs font-bold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors rounded-lg text-slate-400 dark:text-slate-500 border border-transparent uppercase tracking-wider">Quarterly</button>
                <button class="px-5 py-2 text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm transition-colors flex items-center gap-2 uppercase tracking-widest">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Pick Dates
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Inflow --}}
            <div class="bg-white dark:bg-slate-800 p-8 rounded-xl border border-slate-200 dark:border-slate-700 relative overflow-hidden flex flex-col items-center justify-center text-center shadow-sm">
                <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl flex items-center justify-center text-emerald-600 dark:text-emerald-500 border border-emerald-100 dark:border-emerald-800/30 mb-4 shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
                <p class="text-[10px] font-bold text-slate-400 tracking-widest mb-2 uppercase">Revenue</p>
                <h4 class="text-4xl font-black text-slate-900 dark:text-white">$0.00</h4>
                <p class="text-[10px] text-emerald-600 dark:text-emerald-500 font-bold mt-4 flex items-center gap-1 uppercase tracking-wider">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                    0% vs Last Period
                </p>
            </div>

            {{-- Outflow --}}
            <div class="bg-white dark:bg-slate-800 p-8 rounded-xl border border-slate-200 dark:border-slate-700 relative overflow-hidden flex flex-col items-center justify-center text-center shadow-sm">
                <div class="w-12 h-12 bg-rose-50 dark:bg-rose-900/20 rounded-xl flex items-center justify-center text-rose-600 dark:text-rose-500 border border-rose-100 dark:border-rose-800/30 mb-4 shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                </div>
                <p class="text-[10px] font-bold text-slate-400 tracking-widest mb-2 uppercase">Costs & Expenses</p>
                <h4 class="text-4xl font-black text-slate-900 dark:text-white">$0.00</h4>
                <p class="text-[10px] text-rose-600 dark:text-rose-500 font-bold mt-4 flex items-center gap-1 uppercase tracking-wider">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                    0% vs Last Period
                </p>
            </div>
        </div>

        {{-- Profit Card --}}
        <div>
            <div class="bg-blue-600 p-10 rounded-xl shadow-lg border border-blue-500 text-center text-white relative overflow-hidden">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.1),transparent)] pointer-events-none"></div>
                <div class="relative z-10">
                    <p class="text-[10px] font-bold text-blue-100 uppercase tracking-[0.2em] mb-4">Total Earning</p>
                    <h3 class="text-6xl font-black mb-8 leading-none tracking-tight">$0.00</h3>
                    <div class="max-w-md mx-auto">
                        <div class="w-full bg-blue-900/40 h-2.5 rounded-full overflow-hidden border border-blue-400/30 p-0.5">
                            <div class="bg-white h-full w-[2%] rounded-full shadow-[0_0_12px_rgba(255,255,255,0.5)]"></div>
                        </div>
                        <p class="text-xs text-blue-100 mt-5 font-bold uppercase tracking-widest">Profit Margin: 0%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>

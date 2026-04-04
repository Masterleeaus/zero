<x-layouts.admin>
    <x-slot:header>Personnel Financials</x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        {{-- Generate Payroll Form --}}
        <div class="relative overflow-hidden bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm h-fit group hover:shadow-xl transition-all duration-500">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center border border-blue-100 dark:border-blue-800/30">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] leading-none mb-1">New Issuance</h3>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest leading-none italic">Debit Entity</p>
                </div>
            </div>

            <form action="{{ route('admin.payroll.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="space-y-4">
                    <div class="group/field">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 px-1 group-focus-within/field:text-blue-500 transition-colors">Team Member</label>
                        <select name="employee_id" required 
                                class="w-full bg-slate-50/50 dark:bg-slate-800/50 border-slate-200/60 dark:border-slate-700 rounded-2xl text-sm font-bold text-slate-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" class="dark:bg-slate-900">{{ $emp->user->name }} • {{ $emp->designation }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="group/field">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 px-1 group-focus-within/field:text-blue-500 transition-colors">Cycle Period</label>
                        <input type="month" name="month" value="{{ date('Y-m') }}" required 
                               class="w-full bg-slate-50/50 dark:bg-slate-800/50 border-slate-200/60 dark:border-slate-700 rounded-2xl text-sm font-bold text-slate-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="group/field">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 px-1 group-focus-within/field:text-emerald-500 transition-colors">Bonus Accent</label>
                            <input type="number" step="0.01" name="bonus" value="0.00" 
                                   class="w-full bg-slate-50/50 dark:bg-slate-800/50 border-slate-200/60 dark:border-slate-700 rounded-2xl text-sm font-bold text-emerald-600 dark:text-emerald-400 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all">
                        </div>
                        <div class="group/field">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 px-1 group-focus-within/field:text-rose-500 transition-colors">Adjustment</label>
                            <input type="number" step="0.01" name="deduction" value="0.00" 
                                   class="w-full bg-slate-50/50 dark:bg-slate-800/50 border-slate-200/60 dark:border-slate-700 rounded-2xl text-sm font-bold text-rose-600 dark:text-rose-400 focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 transition-all">
                        </div>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full py-4 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-blue-600 dark:hover:bg-blue-500 hover:text-white shadow-lg shadow-slate-200 dark:shadow-none transition-all duration-300">
                    Execute Disbursement
                </button>
            </form>
        </div>

        {{-- Payroll History --}}
        <div class="lg:col-span-2 relative flex flex-col bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm overflow-hidden group hover:shadow-xl transition-all duration-500">
            <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] leading-none mb-1">Payment Ledger</h3>
                    <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest leading-none">Historical Records</p>
                </div>
                <div class="px-3 py-1 bg-slate-50 dark:bg-slate-800 rounded-full border border-slate-100 dark:border-slate-700">
                    <span class="text-[9px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">{{ $payrolls->count() }} ENTRIES</span>
                </div>
            </div>
            
            <div class="overflow-x-auto flex-1">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-50/30 dark:bg-slate-800/30 text-left border-b border-slate-100 dark:border-slate-800">
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] w-1/3">Recipient</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Cycle</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Net Value</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                        @forelse($payrolls as $payroll)
                        <tr class="group/row hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all duration-300">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center border border-slate-100 dark:border-slate-700 group-hover/row:border-blue-100 dark:group-hover/row:border-blue-800/30 transition-colors">
                                        <span class="text-sm font-black text-slate-400 group-hover/row:text-blue-500">{{ substr($payroll->employee->user->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-900 dark:text-white leading-none mb-1 group-hover/row:text-blue-600 transition-colors">{{ $payroll->employee->user->name }}</p>
                                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ $payroll->employee->designation }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">{{ date('F Y', strtotime($payroll->month)) }}</span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <span class="text-sm font-black text-slate-900 dark:text-white">${{ number_format($payroll->net_salary, 2) }}</span>
                            </td>
                            <td class="px-8 py-5 text-center">
                                @php
                                    $statusColor = match($payroll->status) {
                                        'paid' => 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/30',
                                        'pending' => 'bg-amber-50 text-amber-600 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/30',
                                        default => 'bg-slate-50 text-slate-600 border-slate-100 dark:bg-slate-900/20 dark:text-slate-400 dark:border-slate-800/30'
                                    };
                                @endphp
                                <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border {{ $statusColor }}">
                                    {{ $payroll->status }}
                                </span>
                            </td>
                                            <button type="submit" class="px-3 py-1.5 text-[10px] font-bold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:hover:bg-emerald-900/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50 rounded-md transition-all uppercase tracking-widest whitespace-nowrap">
                                                Complete
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 mr-2 whitespace-nowrap uppercase tracking-widest italic">Paid {{ optional($pr->paid_at)->format('M d') }}</span>
                                    @endif
                                    <a href="{{ route('admin.payroll.show', $pr) }}" class="px-3 py-1.5 text-[10px] font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/40 dark:text-blue-400 border border-blue-200 dark:border-blue-800/50 rounded-md transition-all uppercase tracking-widest whitespace-nowrap">
                                        Receipt
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="w-12 h-12 mx-auto bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center text-slate-400 mb-4 border border-slate-200 dark:border-slate-700 shadow-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </div>
                                <h3 class="font-bold text-slate-900 dark:text-white mb-1">No payroll records yet</h3>
                                <p class="text-slate-500 dark:text-slate-400 text-sm">Generate your first pay slip using the form.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($payrolls->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                {{ $payrolls->links() }}
            </div>
            @endif
        </div>
    </div>
</x-layouts.admin>

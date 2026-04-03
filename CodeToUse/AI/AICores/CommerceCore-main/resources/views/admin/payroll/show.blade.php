<x-layouts.admin>
    <x-slot:header>Payslip Details</x-slot:header>

    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.payroll.index') }}" class="text-sm font-medium text-surface-500 hover:text-primary-600 transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            Back to Payroll List
        </a>
        <div class="flex gap-3">
            <button onclick="window.print()" class="btn btn-secondary border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-700 dark:text-surface-200 hover:bg-surface-50 dark:hover:bg-surface-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                Print / PDF
            </button>
            @if($payroll->status === 'pending')
            <form action="{{ route('admin.payroll.update', $payroll) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-primary bg-emerald-500 hover:bg-emerald-600 shadow-emerald-500/20 border-0 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Mark as Paid
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Payslip Document --}}
    <div class="max-w-4xl mx-auto bg-white dark:bg-[#1a1c23] shadow-xl border border-surface-200 dark:border-surface-800 rounded-3xl overflow-hidden print:shadow-none print:border-none print:bg-white print:text-black">
        
        {{-- Header Strip --}}
        <div class="h-4 w-full bg-gradient-to-r from-primary-500 via-indigo-500 to-purple-500"></div>
        
        <div class="p-10 sm:p-16">
            {{-- Header/Logo Section --}}
            <div class="flex flex-col sm:flex-row justify-between items-start mb-16 gap-8">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-500 to-violet-600 flex items-center justify-center shadow-lg shadow-primary-500/30">
                            <span class="text-white font-bold text-xl">{{ substr($payroll->employee->store->name ?? 'CC', 0, 1) }}</span>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-surface-900 dark:text-white print:text-black tracking-tight">{{ $payroll->employee->store->name ?? 'CommerceCore' }}</h2>
                            <p class="text-xs text-surface-500 uppercase tracking-widest font-bold mt-1">Salary Payslip</p>
                        </div>
                    </div>
                </div>
                
                <div class="text-left sm:text-right">
                    <div class="inline-flex px-4 py-1.5 rounded-lg border border-surface-200 dark:border-surface-700 bg-surface-50 dark:bg-surface-800/50 print:bg-gray-100 print:text-black">
                        <p class="text-sm font-black text-surface-800 dark:text-white print:text-black">Pay Period: <span class="text-primary-600 dark:text-primary-400">{{ date('F Y', strtotime($payroll->month.'-01')) }}</span></p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-16">
                {{-- Employee Detail --}}
                <div class="space-y-4">
                    <h3 class="text-xs font-bold text-surface-400 uppercase tracking-widest border-b border-surface-200 dark:border-surface-700 pb-2">Employee Details</h3>
                    <div class="grid grid-cols-2 gap-y-4 gap-x-8">
                        <div>
                            <p class="text-[10px] font-bold text-surface-400 uppercase tracking-wider mb-1">Name</p>
                            <p class="text-sm font-semibold text-surface-900 dark:text-white print:text-black">{{ $payroll->employee->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-surface-400 uppercase tracking-wider mb-1">Employee ID</p>
                            <p class="text-sm font-semibold text-surface-800 dark:text-white print:text-black">{{ $payroll->employee->employee_id }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-surface-400 uppercase tracking-wider mb-1">Designation</p>
                            <p class="text-sm font-semibold text-surface-800 dark:text-white print:text-black">{{ $payroll->employee->designation }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-surface-400 uppercase tracking-wider mb-1">Join Date</p>
                            <p class="text-sm font-semibold text-surface-800 dark:text-white print:text-black">{{ $payroll->employee->joining_date->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
                
                {{-- Payment Detail --}}
                <div class="space-y-4 md:pl-8 md:border-l border-surface-200 dark:border-surface-700">
                    <h3 class="text-xs font-bold text-surface-400 uppercase tracking-widest border-b border-surface-200 dark:border-surface-700 pb-2">Payment Details</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-[10px] font-bold text-surface-400 uppercase tracking-wider mb-1">Status</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $payroll->status === 'paid' ? 'bg-emerald-100 text-emerald-700 print:text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 print:text-amber-700 dark:bg-amber-500/10 dark:text-amber-400' }}">
                                {{ $payroll->status }}
                            </span>
                        </div>
                        @if($payroll->paid_at)
                        <div>
                            <p class="text-[10px] font-bold text-surface-400 uppercase tracking-wider mb-1">Paid On</p>
                            <p class="text-sm font-semibold text-surface-800 dark:text-white print:text-black">{{ $payroll->paid_at->format('F d, Y h:i A') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Salary Breakdown Table --}}
            <div class="bg-surface-50 dark:bg-surface-900/40 print:bg-transparent rounded-2xl border border-surface-200 dark:border-surface-800 overflow-hidden mb-12">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-surface-100 dark:bg-surface-800/80 print:bg-gray-100 border-b border-surface-200 dark:border-surface-700">
                            <th class="px-6 py-4 text-[10px] font-bold text-surface-500 uppercase tracking-widest print:text-black">Earnings</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-surface-500 uppercase tracking-widest text-right print:text-black">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-200 dark:divide-surface-700/50">
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-surface-800 dark:text-surface-200 print:text-black">Basic Salary</td>
                            <td class="px-6 py-4 text-sm font-semibold text-surface-900 dark:text-white print:text-black text-right">${{ number_format($payroll->basic_salary, 2) }}</td>
                        </tr>
                        @if($payroll->bonus > 0)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-surface-800 dark:text-surface-200 print:text-black">Bonus / Allowances</td>
                            <td class="px-6 py-4 text-sm font-semibold text-emerald-600 dark:text-emerald-400 text-right">+ ${{ number_format($payroll->bonus, 2) }}</td>
                        </tr>
                        @endif
                        @if($payroll->deduction > 0)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium print:text-black text-rose-600 dark:text-rose-400">Deductions</td>
                            <td class="px-6 py-4 text-sm font-semibold text-rose-600 dark:text-rose-400 text-right">- ${{ number_format($payroll->deduction, 2) }}</td>
                        </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="bg-primary-50 dark:bg-primary-900/10 print:bg-gray-100 border-t-2 border-primary-200 dark:border-primary-500/30">
                            <td class="px-6 py-5 text-right font-bold text-surface-900 dark:text-white print:text-black tracking-tight">Net Salary Payable:</td>
                            <td class="px-6 py-5 text-right font-black text-xl text-primary-600 dark:text-primary-400 print:text-black">${{ number_format($payroll->net_salary, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Footer Notes --}}
            <div class="text-center mt-16 pt-8 border-t border-surface-200 dark:border-surface-700 text-surface-400 text-xs">
                <p>This is a system generated document and does not require a signature.</p>
                <p class="mt-1 font-medium">Generated by {{ config('app.name') }}</p>
            </div>
            
        </div>
    </div>
</x-layouts.admin>

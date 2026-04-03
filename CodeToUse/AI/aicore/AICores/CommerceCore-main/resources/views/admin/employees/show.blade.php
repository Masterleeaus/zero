<x-layouts.admin>
    <x-slot:header>Employee Profile</x-slot:header>

    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.employees.index') }}" class="text-sm font-medium text-slate-500 hover:text-blue-600 transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            Back to Staff List
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Profile Card --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm text-center relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-br from-blue-500/20 to-indigo-500/20 dark:from-blue-500/10 dark:to-indigo-500/10 border-b border-slate-200 dark:border-slate-700/50"></div>
                
                <div class="relative z-10">
                    <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 mx-auto flex items-center justify-center text-white text-3xl font-black shadow-md shadow-blue-500/20 mb-4 border-4 border-white dark:border-slate-800">
                        {{ strtoupper(substr($employee->user->name, 0, 1)) }}
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $employee->user->name }}</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 font-medium tracking-wide mt-1">{{ $employee->designation }}</p>
                    
                    <span class="inline-flex items-center px-2.5 py-1 mt-3 rounded-md text-xs font-bold uppercase tracking-wider {{ $employee->status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400 border border-green-200 dark:border-green-800/40' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700' }}">
                        {{ $employee->status }}
                    </span>
                </div>

                <div class="mt-8 space-y-4 text-left border-t border-slate-200 dark:border-slate-700 pt-6">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Employee ID</p>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $employee->employee_id }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Email</p>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $employee->user->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Date Joined</p>
                        <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $employee->joining_date->format('F d, Y') }} <span class="text-slate-500 dark:text-slate-400 text-xs font-normal ml-1">({{ $employee->joining_date->diffForHumans() }})</span></p>
                    </div>
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-700/50">
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Base Compensation</p>
                        <p class="text-lg font-black text-blue-600 dark:text-blue-400">${{ number_format($employee->basic_salary, 2) }} <span class="text-xs text-slate-500 font-normal">/mo</span></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Details & History --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Payroll History --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="font-bold text-slate-800 dark:text-white">Compensation History</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Recent payslips and salary records</p>
                    </div>
                    <button class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40 border border-blue-200 dark:border-blue-800/50 rounded-lg text-sm font-semibold transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Adjust Salary
                    </button>
                </div>
                
                @if($employee->payrolls && $employee->payrolls->count() > 0)
                    <div class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        @foreach($employee->payrolls->sortByDesc('created_at')->take(5) as $pay)
                            <div class="p-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-600 shadow-sm">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900 dark:text-white">{{ date('F Y', strtotime($pay->month.'-01')) }} Salary</h4>
                                        <p class="text-xs text-slate-500 mt-0.5">Basic: ${{ number_format($pay->basic_salary,2) }} &bull; Net: <span class="font-medium text-slate-700 dark:text-slate-300">${{ number_format($pay->net_salary,2) }}</span></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider {{ $pay->status === 'paid' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400 border border-green-200 dark:border-green-800/40' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-500 border border-yellow-200 dark:border-yellow-800/40' }}">
                                        {{ $pay->status }}
                                    </span>
                                    <a href="{{ route('admin.payroll.show', $pay) }}" class="p-2 text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors bg-white hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800 rounded-md border border-slate-200 dark:border-slate-700 shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-10 flex flex-col items-center justify-center text-slate-500">
                        <svg class="w-10 h-10 mb-3 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <p class="text-sm">No payroll records generated yet.</p>
                    </div>
                @endif
            </div>
            
            {{-- Attendance Overview --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="font-bold text-slate-800 dark:text-white">Recent Attendance</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Last 7 recorded shifts</p>
                </div>
                @if($employee->attendances && $employee->attendances->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Clock In</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Clock Out</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                @foreach($employee->attendances->sortByDesc('date')->take(7) as $att)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                        <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($att->date)->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('h:i A') : '-' }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('h:i A') : '-' }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider {{ $att->status === 'present' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400 border border-green-200 dark:border-green-800/40' : ($att->status === 'half-day' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-500 border border-amber-200 dark:border-amber-800/40' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400 border border-red-200 dark:border-red-800/40') }}">
                                                {{ $att->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-10 flex flex-col items-center justify-center text-slate-500">
                        <svg class="w-10 h-10 mb-3 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <p class="text-sm">No attendance records found.</p>
                    </div>
                @endif
            </div>
            
        </div>
    </div>
</x-layouts.admin>

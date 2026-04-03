<x-layouts.admin>
    <x-slot:header>Employee Management</x-slot:header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Add Employee Form --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 h-fit shadow-sm">
            <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-5">Onboard New Employee</h3>
            <form action="{{ route('admin.employees.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5 font-semibold uppercase tracking-wider">Account User</label>
                    <select name="user_id" required class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-900 dark:text-white transition-colors focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm outline-none px-3 py-2">
                        @foreach(\App\Models\User::all() as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5 font-semibold uppercase tracking-wider">Employee ID / Code</label>
                    <input type="text" name="employee_id" required value="EMP-{{ rand(1000, 9999) }}" class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-900 dark:text-white transition-colors focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm outline-none px-3 py-2">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1.5 font-semibold uppercase tracking-wider">Designation</label>
                        <input type="text" name="designation" placeholder="Sales agent" required class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-900 dark:text-white transition-colors focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm outline-none px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1.5 font-semibold uppercase tracking-wider">Basic Salary</label>
                        <input type="number" name="basic_salary" value="0.00" required class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-900 dark:text-white transition-colors focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm outline-none px-3 py-2">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5 font-semibold uppercase tracking-wider">Joining Date</label>
                    <input type="date" name="joining_date" value="{{ date('Y-m-d') }}" required class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-900 dark:text-white transition-colors focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm outline-none px-3 py-2">
                </div>
                <button type="submit" class="w-full py-2.5 mt-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors shadow-sm focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:focus:ring-offset-slate-900">
                    Register Staff
                </button>
            </form>
        </div>

        {{-- Employee List --}}
        <div class="md:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">Active Staff</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Employee Details</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Join Date</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Basic Salary</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        @foreach($employees as $emp)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-[11px] font-bold text-blue-600 dark:text-blue-400 mb-0.5 uppercase tracking-wider">{{ $emp->employee_id }}</p>
                                <a href="{{ route('admin.employees.show', $emp) }}" class="text-sm font-semibold text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ $emp->user->name }}</a>
                                <p class="text-[11px] text-slate-500 mt-0.5 tracking-tight">{{ $emp->designation }}</p>
                            </td>
                            <td class="px-6 py-4 text-center text-sm text-slate-600 dark:text-slate-400">
                                {{ $emp->joining_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">${{ number_format($emp->basic_salary, 2) }}</p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider
                                    {{ $emp->status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400 border border-green-200 dark:border-green-800/40' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700' }}">
                                    {{ $emp->status }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                {{ $employees->links() }}
            </div>
        </div>
    </div>
</x-layouts.admin>

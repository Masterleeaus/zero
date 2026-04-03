<x-layouts.admin>
    <x-slot:header>Time Off</x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- Request Leave Form --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 h-fit shadow-sm">
            <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">Request Absence</h3>
            <form action="{{ route('admin.leaves.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs text-slate-400 mb-1 font-bold">Category</label>
                    <select name="type" required class="w-full text-sm border-slate-200 dark:border-slate-700 rounded-lg dark:bg-slate-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                        <option value="Sick Leave">Sick Leave</option>
                        <option value="Casual Leave">Casual Leave</option>
                        <option value="Annual Leave">Annual Leave</option>
                        <option value="Maternity/Paternity">Maternity/Paternity</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-slate-400 mb-1 font-bold">From Date</label>
                        <input type="date" name="start_date" required class="w-full text-sm border-slate-200 dark:border-slate-700 rounded-lg dark:bg-slate-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1 font-bold">Until Date</label>
                        <input type="date" name="end_date" required class="w-full text-sm border-slate-200 dark:border-slate-700 rounded-lg dark:bg-slate-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1 font-bold">Note</label>
                    <textarea name="reason" placeholder="Briefly explain..." rows="3" class="w-full text-sm border-slate-200 dark:border-slate-700 rounded-lg dark:bg-slate-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition-colors focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:focus:ring-offset-slate-900 shadow-sm uppercase text-xs tracking-widest">
                    Submit Request
                </button>
            </form>
        </div>

        {{-- Leave History --}}
        <div class="lg:col-span-3 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Team Requests</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Person</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Category</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Period</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] text-right">Condition</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] text-right">Options</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        @forelse($leaves as $leave)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $leave->user->name }}</p>
                                <p class="text-xs text-slate-500">{{ Str::limit($leave->reason, 40) }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-slate-700 dark:text-slate-300">
                                {{ $leave->type }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">From: {{ $leave->start_date->format('M d, Y') }}</p>
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">To: {{ $leave->end_date->format('M d, Y') }}</p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @php
                                    $sc = match($leave->status) {
                                        'approved' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400 border-green-200 dark:border-green-800/40',
                                        'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400 border-red-200 dark:border-red-800/40',
                                        default => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-500 border-yellow-200 dark:border-yellow-800/40',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider border {{ $sc }}">
                                    {{ $leave->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($leave->status === 'pending')
                                    <div class="flex items-center justify-end gap-2">
                                        <form action="{{ route('admin.leaves.update', $leave) }}" method="POST">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="p-1.5 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 dark:text-green-500 border border-transparent hover:border-green-200 dark:hover:border-green-800/50 rounded-md transition-colors" title="Approve">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.leaves.update', $leave) }}" method="POST">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 dark:text-red-500 border border-transparent hover:border-red-200 dark:hover:border-red-800/50 rounded-md transition-colors" title="Reject">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 font-semibold italic">Closed</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="w-12 h-12 mx-auto bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center text-slate-400 mb-4 border border-slate-200 dark:border-slate-700 shadow-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <h3 class="font-bold text-slate-900 dark:text-white mb-1">No leave applications</h3>
                                <p class="text-slate-500 dark:text-slate-400 text-sm">There are currently no leave requests to review.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($leaves->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                {{ $leaves->links() }}
            </div>
            @endif
        </div>
    </div>
</x-layouts.admin>

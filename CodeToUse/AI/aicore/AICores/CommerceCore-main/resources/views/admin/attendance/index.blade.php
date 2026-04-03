<x-layouts.admin>
    <x-slot:header>Attendance Tracking</x-slot:header>

    <div class="space-y-6">
        {{-- Clock In/Out Section --}}
        <div class="bg-white dark:bg-slate-800 p-8 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Daily Attendance</h3>
                <p class="text-sm text-slate-500 font-medium">Current Time: <span class="font-bold text-blue-600 dark:text-blue-500">{{ now()->format('h:i A') }}</span></p>
                @if($todayAttendance)
                    <p class="mt-4 inline-flex items-center text-xs font-semibold text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/40 border border-green-200 dark:border-green-800/40 px-3 py-1.5 rounded-full">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 shadow-[0_0_8px_rgba(34,197,94,0.6)] animate-pulse"></span>
                        Active Session: Started at {{ \Carbon\Carbon::parse($todayAttendance->clock_in)->format('h:i A') }}
                    </p>
                @endif
            </div>

            <div class="flex gap-4">
                @if(!$todayAttendance)
                    <form action="{{ route('admin.attendance.clock_in') }}" method="POST">
                        @csrf
                        <button type="submit" class="group relative px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl transition-all shadow-sm active:scale-95 flex items-center gap-3">
                            <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                            Clock In
                        </button>
                    </form>
                @elseif(!$todayAttendance->clock_out)
                    <form action="{{ route('admin.attendance.clock_out') }}" method="POST">
                        @csrf
                        <button type="submit" class="group relative px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all shadow-sm active:scale-95 flex items-center gap-3">
                            <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                            Clock Out
                        </button>
                    </form>
                @else
                    <div class="px-6 py-3 bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 font-bold rounded-xl border border-slate-200 dark:border-slate-700 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Shift Ended
                    </div>
                @endif
            </div>
        </div>

        {{-- Attendance Logs --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white uppercase tracking-wider">Attendance Logs</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Staff Member</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Clock In</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Clock Out</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Working Hours</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        @forelse($attendances as $log)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 font-bold text-xs mr-3 border border-blue-200 dark:border-blue-800/30">
                                        {{ substr($log->user->name, 0, 1) }}
                                    </div>
                                    <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $log->user->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                {{ $log->date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-bold text-green-600 dark:text-green-500">{{ \Carbon\Carbon::parse($log->clock_in)->format('h:i A') }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($log->clock_out)
                                    <span class="text-sm font-bold text-red-600 dark:text-red-500">{{ \Carbon\Carbon::parse($log->clock_out)->format('h:i A') }}</span>
                                @else
                                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider italic">Expected...</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($log->clock_out)
                                    @php
                                        $in = \Carbon\Carbon::parse($log->clock_in);
                                        $out = \Carbon\Carbon::parse($log->clock_out);
                                        $duration = $out->diffInMinutes($in);
                                        $hours = floor($duration / 60);
                                        $mins = $duration % 60;
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">{{ $hours }}h {{ $mins }}m</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-mono text-xs text-slate-400">
                                {{ $log->ip_address }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="w-12 h-12 mx-auto bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center text-slate-400 mb-4 border border-slate-200 dark:border-slate-700 shadow-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <h3 class="font-bold text-slate-900 dark:text-white mb-1">No attendance records</h3>
                                <p class="text-slate-500 dark:text-slate-400 text-sm">No one has clocked in today.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($attendances->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                {{ $attendances->links() }}
            </div>
            @endif
        </div>
    </div>
</x-layouts.admin>

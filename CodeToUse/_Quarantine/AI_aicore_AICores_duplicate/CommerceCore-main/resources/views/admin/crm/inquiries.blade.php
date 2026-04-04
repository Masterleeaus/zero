<x-layouts.admin>
    <x-slot:header>Customer Inquiries</x-slot:header>

    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white uppercase tracking-wider">Store Inquiries</h2>
                <div class="flex gap-2">
                    <span class="px-2.5 py-1 bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-500 text-xs font-bold rounded-md border border-yellow-200 dark:border-yellow-800/40 uppercase tracking-wider">{{ $inquiries->where('status', 'new')->count() }} New</span>
                    <span class="px-2.5 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-400 text-xs font-bold rounded-md border border-blue-200 dark:border-blue-800/40 uppercase tracking-wider">{{ $inquiries->total() }} Total</span>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Sender</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        @forelse($inquiries as $inquiry)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900 dark:text-white text-sm">{{ $inquiry->name }}</div>
                                <div class="text-xs text-slate-500">{{ $inquiry->email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $inquiry->subject ?: 'No Subject' }}</div>
                                <div class="text-xs text-slate-500 truncate max-w-xs">{{ Str::limit($inquiry->message, 45) }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $isc = match($inquiry->status) {
                                        'new' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-500 border border-yellow-200 dark:border-yellow-800/40',
                                        'read' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-400 border border-blue-200 dark:border-blue-800/40',
                                        'replied' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400 border border-green-200 dark:border-green-800/40',
                                        'closed' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700',
                                        default => 'bg-slate-100 text-slate-700 border border-slate-200'
                                    };
                                @endphp
                                <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider {{ $isc }}">{{ $inquiry->status }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-500">{{ $inquiry->created_at->diffForHumans() }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2" x-data="{ open: false }">
                                    <button @click="open = !open" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 dark:hover:text-blue-400 border border-transparent hover:border-blue-200 dark:hover:border-blue-800/50 rounded-md transition-colors" title="View Details">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    
                                    {{-- Status Update Form Hidden --}}
                                    <form action="{{ route('admin.crm.inquiries.status', $inquiry) }}" method="POST" id="status-form-{{ $inquiry->id }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" id="status-input-{{ $inquiry->id }}">
                                    </form>

                                    {{-- Modal/Overlay for full view --}}
                                    <template x-if="open">
                                        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
                                            <div @click.away="open = false" class="bg-white dark:bg-slate-800 w-full max-w-xl rounded-xl overflow-hidden shadow-2xl border border-slate-200 dark:border-slate-700">
                                                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                                                    <div class="flex justify-between items-start mb-4">
                                                        <div class="text-left">
                                                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ $inquiry->subject }}</h3>
                                                            <p class="text-sm text-slate-500 mt-1">From: {{ $inquiry->name }} ({{ $inquiry->email }})</p>
                                                        </div>
                                                        <button @click="open = false" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </button>
                                                    </div>
                                                    <div class="bg-slate-50 dark:bg-slate-900/50 p-6 rounded-lg text-slate-700 dark:text-slate-300 text-sm leading-relaxed whitespace-pre-wrap border border-slate-200 dark:border-slate-700 text-left">{{ $inquiry->message }}</div>
                                                </div>
                                                <div class="p-6 bg-slate-50 dark:bg-slate-900/50 flex flex-wrap gap-3 items-center justify-between border-t border-slate-200 dark:border-slate-700">
                                                    <div class="flex gap-2">
                                                        @foreach(['read', 'replied', 'closed'] as $s)
                                                            <button 
                                                                @click="document.getElementById('status-input-{{ $inquiry->id }}').value = '{{ $s }}'; document.getElementById('status-form-{{ $inquiry->id }}').submit()"
                                                                class="px-3 py-1.5 rounded-md text-xs font-semibold uppercase tracking-wider transition-colors border {{ $inquiry->status === $s ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700' }}"
                                                            >Mark as {{ $s }}</button>
                                                        @endforeach
                                                    </div>
                                                    <a href="mailto:{{ $inquiry->email }}?subject=Re: {{ $inquiry->subject }}" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:focus:ring-offset-slate-900">Compose Reply</a>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="w-12 h-12 mx-auto bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center text-slate-400 mb-4 border border-slate-200 dark:border-slate-700 shadow-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                </div>
                                <h3 class="font-bold text-slate-900 dark:text-white mb-1">No inquiries yet</h3>
                                <p class="text-slate-500 dark:text-slate-400 text-sm">When customers contact you, their messages will appear here.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($inquiries->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                    {{ $inquiries->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>

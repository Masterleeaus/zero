<x-layouts.admin>
    <x-slot:header>Marketing & Growth</x-slot:header>

    <div class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Create Campaign --}}
            <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 h-fit shadow-sm">
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">New Promotion</h3>
                <form action="{{ route('admin.marketing.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs text-slate-400 mb-1 font-bold">Promotion Name</label>
                        <input type="text" name="name" required placeholder="Summer Discount" class="w-full text-sm border-slate-200 dark:border-slate-700 rounded-lg dark:bg-slate-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-slate-400 mb-1 font-bold">Channel</label>
                            <select name="type" required class="w-full text-sm border-slate-200 dark:border-slate-700 rounded-lg dark:bg-slate-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                <option value="sms">SMS Text</option>
                                <option value="email">Email</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="push">Notification</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1 font-bold">Who to Reach</label>
                            <select name="target_rank" class="w-full text-sm border-slate-200 dark:border-slate-700 rounded-lg dark:bg-slate-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Everyone</option>
                                <option value="VIP">Top Customers</option>
                                <option value="regular">Regulars</option>
                                <option value="inactive">Paused Customers</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1 font-bold">Your Wordings</label>
                        <textarea name="message" rows="4" required placeholder="Write your message..." class="w-full text-sm border-slate-200 dark:border-slate-700 rounded-lg dark:bg-slate-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition-all shadow-sm focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:focus:ring-offset-slate-900 uppercase text-xs tracking-widest">
                        Send Now
                    </button>
                    <p class="text-[10px] text-slate-400 text-center font-medium italic">Messages are queued for immediate delivery.</p>
                </form>
            </div>

            {{-- Tracking & Intelligence --}}
            <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 h-fit shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Conversion Tracking</h3>
                    <span class="px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-500 text-[9px] font-black uppercase border border-blue-500/20">Active</span>
                </div>
                <form action="{{ route('admin.marketing.settings') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs text-slate-400 mb-1 font-bold">Facebook Pixel ID</label>
                        <div class="relative">
                            <input type="text" name="facebook_pixel_id" value="{{ auth()->user()->store->facebook_pixel_id }}" placeholder="e.g. 1234567890" class="w-full text-sm border-slate-200 dark:border-slate-700 rounded-lg dark:bg-slate-900 dark:text-white pr-10 focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                @if(auth()->user()->store->facebook_pixel_id)
                                <div class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></div>
                                @else
                                <div class="w-2 h-2 rounded-full bg-slate-200 dark:bg-slate-700"></div>
                                @endif
                            </div>
                        </div>
                        <p class="mt-2 text-[10px] text-slate-400 leading-relaxed italic">
                            Used to track store visitors and sales performance on social media.
                        </p>
                    </div>
                    <button type="submit" class="w-full py-3 bg-slate-900 dark:bg-slate-700 text-white font-bold rounded-lg transition-all shadow-sm active:scale-95 text-xs uppercase tracking-widest">
                        Save Tracking
                    </button>
                    @if(auth()->user()->store->facebook_pixel_id)
                    <div class="p-3 rounded-xl bg-emerald-500/5 border border-emerald-500/10 flex items-start gap-3">
                        <svg class="w-4 h-4 text-emerald-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-[10px] font-bold text-emerald-600/80 dark:text-emerald-400/80 leading-snug">
                            Pixel Protocol Active. Standard 'PageView' events are being transmitted from your storefront.
                        </p>
                    </div>
                    @endif
                </form>
            </div>
            <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Past Outreach</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Activity</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Pathway</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">Reached</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Sent On</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                            @foreach($campaigns as $camp)
                            <tr class="hover:bg-surface-50 dark:hover:bg-surface-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $camp->name }}</p>
                                    <p class="text-[10px] text-slate-400 font-medium line-clamp-1 italic">{{ $camp->message }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase text-blue-600 dark:text-blue-400">
                                        {{ $camp->type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-sm text-slate-700 dark:text-slate-300">
                                    {{ number_format($camp->recipients_count) }}
                                </td>
                                <td class="px-6 py-4 text-right text-[10px] font-bold text-slate-500 uppercase whitespace-nowrap">
                                    {{ $camp->created_at->format('M d, H:i') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
                                        {{ $camp->status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>

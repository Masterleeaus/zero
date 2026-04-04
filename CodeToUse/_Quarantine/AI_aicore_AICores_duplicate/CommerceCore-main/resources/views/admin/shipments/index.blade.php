    <x-slot:header>Logistics Center</x-slot:header>

    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        {{-- Top Bar --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div>
                <h2 class="text-3xl font-display font-black text-slate-900 dark:text-white tracking-tight uppercase">Orders Logistics</h2>
                <p class="text-sm font-medium text-slate-500 mt-1.5 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    Track and manage order fulfillment and delivery
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm overflow-hidden min-h-[500px] flex flex-col">
            <div class="overflow-x-auto flex-1 h-full min-h-0">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/30 border-b border-slate-100 dark:border-slate-800 text-left">
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Order #</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Courier Service</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Tracking Identifier</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Shipping Cost</th>
                            <th class="text-center px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Delivery Lifecycle</th>
                            <th class="text-right px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Quick Status Update</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                        @forelse($shipments as $shipment)
                        <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all duration-300">
                            <td class="px-8 py-5 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    </div>
                                    <a href="{{ route('admin.orders.show', $shipment->order) }}" class="text-sm font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700 hover:underline transition-all decoration-2 underline-offset-4 decoration-blue-200">
                                        {{ $shipment->order->order_number }}
                                    </a>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-sm font-bold text-slate-700 dark:text-slate-300">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                    {{ $shipment->courier->name ?? 'None Assigned' }}
                                </div>
                            </td>
                            <td class="px-8 py-5 text-xs font-mono font-bold text-slate-500 tracking-wider">
                                {{ $shipment->tracking_number ?? 'Awaiting ID' }}
                            </td>
                            <td class="px-8 py-5 text-sm font-black text-slate-900 dark:text-white">
                                ${{ number_format($shipment->shipping_cost, 2) }}
                            </td>
                            <td class="px-8 py-5 text-center">
                                @php
                                    $sc = match($shipment->status) {
                                        'delivered' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/30',
                                        'in_transit' => 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 border-blue-100 dark:border-blue-800/30',
                                        'picked' => 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400 border-amber-100 dark:border-amber-800/30',
                                        'returned' => 'bg-rose-50 text-rose-600 dark:bg-rose-900/20 dark:text-rose-400 border-rose-100 dark:border-rose-800/30',
                                        default => 'bg-slate-50 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border-slate-200 dark:border-slate-700',
                                    };
                                @endphp
                                <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border {{ $sc }}">
                                    {{ $shipment->status }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <form action="{{ route('admin.shipments.status', $shipment) }}" method="POST" class="inline-flex items-center justify-end w-full">
                                    @csrf
                                    <div class="relative">
                                        <select name="status" onchange="this.form.submit()" 
                                                class="appearance-none pl-4 pr-10 py-2 text-xs font-bold uppercase tracking-widest bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-all focus:ring-0 focus:border-blue-500 cursor-pointer text-slate-600 dark:text-slate-300">
                                            <option value="picked" {{ $shipment->status === 'picked' ? 'selected' : '' }}>Picked</option>
                                            <option value="in_transit" {{ $shipment->status === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                                            <option value="delivered" {{ $shipment->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                            <option value="returned" {{ $shipment->status === 'returned' ? 'selected' : '' }}>Returned</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M19 9l-7 7-7-7"/></svg>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-8 py-24 text-center">
                                <div class="w-20 h-20 mx-auto bg-slate-50 dark:bg-slate-800/50 rounded-3xl flex items-center justify-center text-slate-300 dark:text-slate-600 mb-6 border border-slate-100 dark:border-slate-800 shadow-inner">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                </div>
                                <h3 class="text-xl font-display font-black text-slate-900 dark:text-white mb-2 uppercase tracking-tight">Logistics Idle</h3>
                                <p class="text-slate-500 dark:text-slate-400 text-sm max-w-xs mx-auto mb-8 font-medium leading-relaxed">No shipments are currently queued for tracking or delivery.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($shipments->hasPages())
                <div class="px-8 py-6 bg-slate-50/30 dark:bg-slate-900/10 border-t border-slate-100 dark:border-slate-800">
                    {{ $shipments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>

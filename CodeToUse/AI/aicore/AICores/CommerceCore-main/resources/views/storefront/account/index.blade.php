<x-layouts.storefront :store="$store">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Profile Sidebar --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white dark:bg-surface-800 p-8 rounded-3xl border border-surface-200 dark:border-surface-700 shadow-sm text-center">
                    <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-br from-indigo-500 to-primary-600 flex items-center justify-center text-white text-3xl font-black mb-4">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h2 class="text-xl font-display font-black text-surface-900 dark:text-white">{{ $user->name }}</h2>
                    <p class="text-sm text-surface-500">{{ $user->email }}</p>
                    
                    {{-- Loyalty Badge --}}
                    <div class="mt-6 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl border border-indigo-100 dark:border-indigo-800">
                        <p class="text-[10px] font-black uppercase text-indigo-500 mb-1">Loyalty Power</p>
                        <div class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <span class="text-2xl font-black text-surface-900 dark:text-white">{{ number_format($user->loyalty_points) }}</span>
                        </div>
                        <p class="text-[10px] text-surface-400 font-bold mt-1">Tier: {{ ucfirst($user->customer_rank) }}</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-surface-800 p-6 rounded-3xl border border-surface-200 dark:border-surface-700 shadow-sm">
                    <h3 class="text-xs font-black uppercase text-surface-400 mb-6 tracking-widest">Rewards Activity</h3>
                    <div class="space-y-4">
                        @foreach($loyaltyHistory as $item)
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[10px] font-black text-surface-800 dark:text-white uppercase">{{ $item->reason }}</p>
                                <p class="text-[9px] text-surface-400 line-clamp-1 italic">{{ $item->created_at->format('M d') }}</p>
                            </div>
                            <span class="text-xs font-black {{ $item->points > 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                                {{ $item->points > 0 ? '+' : '' }}{{ $item->points }}
                            </span>
                        </div>
                        @endforeach
                        @if($loyaltyHistory->isEmpty())
                        <p class="text-[10px] text-surface-400 font-bold uppercase italic text-center py-4">No points events yet.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Activity Content --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- Active Subscriptions --}}
                @if($subscriptions->isNotEmpty())
                <div class="bg-white dark:bg-surface-800 p-8 rounded-3xl border border-surface-200 dark:border-surface-700 shadow-sm">
                    <h3 class="text-xs font-black uppercase text-surface-400 mb-6 tracking-widest">Your Subscriptions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($subscriptions as $sub)
                        <div class="p-4 bg-surface-50 dark:bg-surface-900 border border-surface-100 dark:border-surface-700 rounded-2xl relative overflow-hidden group">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-white dark:bg-surface-800 rounded-xl flex items-center justify-center overflow-hidden">
                                    @if($sub->items->first()->product->image)
                                    <img src="{{ asset('storage/'.$sub->items->first()->product->image) }}" class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-xs font-black text-surface-900 dark:text-white uppercase">{{ $sub->items->first()->product->name }}</h4>
                                    <p class="text-[10px] text-indigo-500 font-bold italic">Billed every 30 days</p>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-between items-end">
                                <div>
                                    <p class="text-[9px] font-bold text-surface-400 uppercase italic">Next Billing</p>
                                    <p class="text-xs font-black">{{ $sub->next_billing_at?->format('F d, Y') ?? 'N/A' }}</p>
                                </div>
                                @if($sub->status !== 'cancelled')
                                <form action="{{ route('storefront.account.sub.cancel', [$store->slug, $sub->id]) }}" method="POST">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Cancel this subscription?')" class="text-[10px] font-black text-rose-500 hover:underline uppercase italic">Cancel Plan</button>
                                </form>
                                @else
                                <span class="text-[10px] font-black text-surface-400 uppercase italic">CANCELLED</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Order History --}}
                <div class="bg-white dark:bg-surface-800 p-8 rounded-3xl border border-surface-200 dark:border-surface-700 shadow-sm">
                    <h3 class="text-xs font-black uppercase text-surface-400 mb-6 tracking-widest">Order History</h3>
                    <div class="space-y-4">
                        @foreach($orders as $order)
                        <div class="flex flex-col md:flex-row md:items-center justify-between p-4 bg-surface-50 dark:bg-surface-900/50 rounded-2xl border border-surface-100 dark:border-surface-800 gap-4">
                            <div>
                                <p class="text-sm font-black text-surface-900 dark:text-white">#{{ $order->order_number }}</p>
                                <p class="text-[10px] text-surface-500 font-bold uppercase italic">{{ $order->created_at->format('M d, Y') }}</p>
                            </div>
                            <div class="flex items-center gap-6">
                                <div class="text-right">
                                    <p class="text-xs font-black italic">${{ number_format($order->total_price, 2) }}</p>
                                    <p class="text-[10px] text-surface-400 font-bold">{{ $order->items->count() }} items</p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase bg-{{ $order->status_color }}-100 text-{{ $order->status_color }}-700">
                                    {{ $order->status }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                        @if($orders->isEmpty())
                        <div class="py-12 text-center">
                            <p class="text-xs font-bold text-surface-400 uppercase italic italic">No completed orders found.</p>
                        </div>
                        @endif
                    </div>
                    <div class="mt-8">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.storefront>

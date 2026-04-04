<x-layouts.admin>
    <x-slot:header>Customer Details</x-slot:header>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-3">
                Customer Profile
            </h2>
            <a href="{{ route('admin.customers.index') }}" class="text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-medium">← Back to List</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Profile Card --}}
            <div class="md:col-span-1 space-y-6">
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm text-center">
                    <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-tr from-blue-500 to-indigo-500 flex items-center justify-center text-white text-3xl font-bold shadow-md shadow-blue-500/20 mb-4">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-lg">{{ $user->name }}</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $user->email }}</p>
                    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-center">
                        <span class="inline-flex px-3 py-1 rounded-md text-xs font-bold uppercase tracking-wider {{ $user->customer_rank === 'gold' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-500 border border-amber-200 dark:border-amber-800/30' : ($user->customer_rank === 'silver' ? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400 border border-slate-200 dark:border-slate-600' : 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 border border-blue-100 dark:border-blue-800/30') }}">Rank: {{ $user->customer_rank }}</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-5">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Total Spent</p>
                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">${{ number_format((float)($user->total_spent ?? 0), 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Orders Placed</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $user->order_count }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Loyalty Points</p>
                        <p class="text-2xl font-bold text-amber-500">{{ $user->loyalty_points }}</p>
                    </div>
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-700/50">
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Member Since</p>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $user->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>

            {{-- Recent Orders --}}
            <div class="md:col-span-2">
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
                    <div class="p-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex items-center justify-between">
                        <h3 class="font-bold text-slate-800 dark:text-white">Order History</h3>
                    </div>
                    
                    <div class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        @forelse($orders as $order)
                        <div class="p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <div>
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-base font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 hover:underline">{{ $order->order_number }}</a>
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">{{ $order->created_at->format('M d, Y h:i A') }} • {{ $order->items_count }} items</p>
                            </div>
                            <div class="text-right flex flex-col sm:items-end gap-1.5">
                                <span class="font-bold text-slate-900 dark:text-white">${{ number_format($order->total_price, 2) }}</span>
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-500',
                                        'paid' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-400',
                                        'shipped' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-400',
                                        'delivered' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
                                        'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-500',
                                    ];
                                    $colorClass = $statusColors[$order->status] ?? 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300';
                                @endphp
                                <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-semibold uppercase tracking-wider {{ $colorClass }}">{{ $order->status }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="p-8 text-center text-slate-500 text-sm">
                            This customer hasn't placed any orders yet.
                        </div>
                        @endforelse
                    </div>
                    
                    @if($orders->hasPages())
                        <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                            {{ $orders->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>

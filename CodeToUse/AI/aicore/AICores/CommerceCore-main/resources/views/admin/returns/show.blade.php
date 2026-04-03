<x-layouts.admin>
    <x-slot:header>Return Details</x-slot:header>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-display font-bold text-surface-800 dark:text-white">Return: {{ $saleReturn->return_number }}</h2>
                <p class="text-sm text-surface-500 mt-1">Requested on {{ $saleReturn->created_at->format('M d, Y h:i A') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.returns.index') }}" class="px-4 py-2 bg-surface-100 hover:bg-surface-200 dark:bg-surface-700 dark:hover:bg-surface-600 text-surface-700 dark:text-surface-300 text-sm font-semibold rounded-xl transition-colors">Voltar(Back)</a>
                
                @if($saleReturn->status === 'pending')
                    <form action="{{ route('admin.returns.reject', $saleReturn) }}" method="POST" onsubmit="return confirm('Are you sure you want to reject this return?');" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-sm font-bold rounded-xl transition-colors">Reject Return</button>
                    </form>
                    <form action="{{ route('admin.returns.approve', $saleReturn) }}" method="POST" onsubmit="return confirm('This will issue a refund and restock items marked as good. Continue?');" class="inline">
                        @csrf
                        <button type="submit" class="px-5 py-2 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-emerald-500/25 transition-all">Approve & Refund</button>
                    </form>
                @else
                    <span class="px-4 py-2 rounded-xl text-sm font-bold {{ $saleReturn->status === 'refunded' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-surface-100 text-surface-700 dark:bg-surface-700 dark:text-surface-300' }}">
                        Status: {{ ucfirst($saleReturn->status) }}
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Return Summary --}}
            <div class="md:col-span-1 space-y-6">
                <div class="bg-white dark:bg-surface-800 p-6 rounded-2xl border border-surface-200 dark:border-surface-700 shadow-sm">
                    <h3 class="text-sm font-bold text-surface-800 dark:text-white mb-4">Synopsis</h3>
                    <ul class="space-y-3 text-sm">
                        <li class="flex justify-between border-b border-surface-100 dark:border-surface-700 pb-2">
                            <span class="text-surface-500">Order Ref</span>
                            <a href="{{ route('admin.orders.show', $saleReturn->order_id) }}" class="font-bold text-primary-600 hover:underline">{{ $saleReturn->order->order_number }}</a>
                        </li>
                        <li class="flex justify-between border-b border-surface-100 dark:border-surface-700 pb-2">
                            <span class="text-surface-500">Reason</span>
                            <span class="font-bold text-surface-800 dark:text-white">{{ $saleReturn->reason }}</span>
                        </li>
                        <li class="flex justify-between pb-2">
                            <span class="text-surface-500">Total Refund</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($saleReturn->total_refund_amount, 2) }}</span>
                        </li>
                    </ul>
                    @if($saleReturn->notes)
                    <div class="mt-4 pt-4 border-t border-surface-100 dark:border-surface-700">
                        <p class="text-xs font-semibold text-surface-500 mb-1">Notes</p>
                        <p class="text-sm text-surface-700 dark:text-surface-300">{{ $saleReturn->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Items --}}
            <div class="md:col-span-2">
                <div class="bg-white dark:bg-surface-800 rounded-2xl border border-surface-200 dark:border-surface-700 overflow-hidden shadow-sm">
                    <div class="p-5 border-b border-surface-100 dark:border-surface-700">
                        <h3 class="font-bold text-surface-800 dark:text-white">Returned Items</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-surface-50 dark:bg-surface-700/50">
                                <tr>
                                    <th class="px-5 py-3 text-xs font-semibold text-surface-500 uppercase">Product</th>
                                    <th class="px-5 py-3 text-xs font-semibold text-surface-500 uppercase">Qty</th>
                                    <th class="px-5 py-3 text-xs font-semibold text-surface-500 uppercase">Condition</th>
                                    <th class="px-5 py-3 text-xs font-semibold text-surface-500 uppercase text-right">Refund Value</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                                @foreach($saleReturn->items as $item)
                                <tr class="hover:bg-surface-50 dark:hover:bg-surface-700/30">
                                    <td class="px-5 py-4">
                                        <p class="text-sm font-semibold text-surface-800 dark:text-white">{{ $item->product->name }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-sm font-bold text-surface-800 dark:text-white">{{ $item->quantity }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-semibold {{ $item->condition === 'good' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ ucfirst($item->condition) }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <span class="text-sm font-bold text-surface-800 dark:text-white">${{ number_format($item->refund_amount, 2) }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 bg-surface-50 dark:bg-surface-700/50 text-right">
                        <span class="text-surface-500 text-sm">Total to Refund:</span>
                        <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400 ml-2">${{ number_format($saleReturn->total_refund_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>

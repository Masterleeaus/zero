<x-layouts.admin>
    <x-slot:header>PO: {{ $purchase->purchase_number }}</x-slot:header>

    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-display font-bold text-surface-800 dark:text-white">{{ $purchase->purchase_number }}</h2>
                <p class="text-sm text-surface-500 mt-1">Created {{ $purchase->created_at->format('M d, Y h:i A') }}</p>
            </div>
            <div class="flex items-center gap-3">
                @if($purchase->status === 'pending' || $purchase->status === 'ordered')
                <form method="POST" action="{{ route('admin.purchases.receive', $purchase) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-emerald-500/25 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Receive Stock
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.purchases.cancel', $purchase) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-rose-500/25 transition-all" onclick="return confirm('Cancel this PO?')">
                        Cancel PO
                    </button>
                </form>
                @endif
                <a href="{{ route('admin.purchases.index') }}" class="text-sm text-surface-500 hover:text-surface-700 dark:hover:text-surface-300 font-medium">← Back</a>
            </div>
        </div>

        {{-- Status & Info --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-surface-800 p-5 rounded-2xl border border-surface-200 dark:border-surface-700 shadow-sm">
                <p class="text-[10px] font-black text-surface-400 uppercase tracking-widest mb-1">Status</p>
                @php
                    $sc = match($purchase->status) {
                        'pending' => 'text-amber-600 dark:text-amber-400',
                        'ordered' => 'text-blue-600 dark:text-blue-400',
                        'received' => 'text-emerald-600 dark:text-emerald-400',
                        'cancelled' => 'text-rose-600 dark:text-rose-400',
                        default => 'text-surface-600',
                    };
                @endphp
                <p class="text-lg font-bold {{ $sc }}">{{ ucfirst($purchase->status) }}</p>
            </div>
            <div class="bg-white dark:bg-surface-800 p-5 rounded-2xl border border-surface-200 dark:border-surface-700 shadow-sm">
                <p class="text-[10px] font-black text-surface-400 uppercase tracking-widest mb-1">Payment</p>
                <p class="text-lg font-bold text-surface-800 dark:text-white">{{ ucfirst($purchase->payment_status) }}</p>
            </div>
            <div class="bg-white dark:bg-surface-800 p-5 rounded-2xl border border-surface-200 dark:border-surface-700 shadow-sm">
                <p class="text-[10px] font-black text-surface-400 uppercase tracking-widest mb-1">Supplier</p>
                <p class="text-sm font-bold text-surface-800 dark:text-white">{{ $purchase->supplier_name }}</p>
                @if($purchase->supplier_email)<p class="text-xs text-surface-400">{{ $purchase->supplier_email }}</p>@endif
            </div>
            <div class="bg-white dark:bg-surface-800 p-5 rounded-2xl border border-surface-200 dark:border-surface-700 shadow-sm border-l-4 border-l-primary-500">
                <p class="text-[10px] font-black text-primary-500 uppercase tracking-widest mb-1">Total Amount</p>
                <p class="text-2xl font-bold text-surface-800 dark:text-white">${{ number_format($purchase->total_amount, 2) }}</p>
            </div>
        </div>

        @if($purchase->received_at)
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-4 rounded-xl">
            <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">✓ Stock received on {{ $purchase->received_at->format('M d, Y h:i A') }} — inventory has been updated.</p>
        </div>
        @endif

        {{-- Line Items --}}
        <div class="bg-white dark:bg-surface-800 rounded-2xl border border-surface-200 dark:border-surface-700 overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-surface-200 dark:border-surface-700">
                <h3 class="text-sm font-bold text-surface-800 dark:text-white">Line Items ({{ $purchase->items->count() }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-surface-50 dark:bg-surface-700/50">
                        <tr>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-surface-500 uppercase tracking-wider">Product</th>
                            <th class="text-center px-6 py-3 text-xs font-semibold text-surface-500 uppercase tracking-wider">Qty</th>
                            <th class="text-right px-6 py-3 text-xs font-semibold text-surface-500 uppercase tracking-wider">Unit Cost</th>
                            <th class="text-right px-6 py-3 text-xs font-semibold text-surface-500 uppercase tracking-wider">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                        @foreach($purchase->items as $item)
                        <tr class="hover:bg-surface-50 dark:hover:bg-surface-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-surface-100 dark:bg-surface-700 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                        @if($item->product->image)
                                        <img src="{{ asset('storage/' . $item->product->image) }}" alt="" class="w-full h-full object-cover">
                                        @else
                                        <svg class="w-5 h-5 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-surface-800 dark:text-white">{{ $item->product->name }}</p>
                                        @if($item->product->sku)<p class="text-xs text-surface-400">SKU: {{ $item->product->sku }}</p>@endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center text-sm font-bold text-surface-800 dark:text-white">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 text-right text-sm text-surface-600 dark:text-surface-300">${{ number_format($item->unit_cost, 2) }}</td>
                            <td class="px-6 py-4 text-right text-sm font-bold text-surface-800 dark:text-white">${{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-surface-50 dark:bg-surface-700/50">
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right text-[10px] font-black text-surface-400 uppercase tracking-widest">Grand Total</td>
                            <td class="px-6 py-4 text-right text-lg font-bold text-surface-800 dark:text-white">${{ number_format($purchase->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if($purchase->notes)
        <div class="bg-white dark:bg-surface-800 p-6 rounded-2xl border border-surface-200 dark:border-surface-700 shadow-sm">
            <h3 class="text-sm font-bold text-surface-800 dark:text-white mb-2">Notes</h3>
            <p class="text-sm text-surface-600 dark:text-surface-400">{{ $purchase->notes }}</p>
        </div>
        @endif
    </div>
</x-layouts.admin>

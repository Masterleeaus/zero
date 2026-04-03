<x-layouts.admin>
    <x-slot:header>Create Return Request</x-slot:header>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-display font-bold text-surface-800 dark:text-white">New Return Request</h2>
                <p class="text-sm text-surface-500 mt-1">Order: {{ $order->order_number }}</p>
            </div>
            <a href="{{ route('admin.orders.show', $order) }}" class="text-sm text-surface-500 hover:text-surface-700 dark:hover:text-surface-300 font-medium">← Back to Order</a>
        </div>

        <form action="{{ route('admin.returns.store') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">

            {{-- Return Details --}}
            <div class="bg-white dark:bg-surface-800 p-6 rounded-2xl border border-surface-200 dark:border-surface-700 shadow-sm">
                <h3 class="text-sm font-bold text-surface-800 dark:text-white mb-4">Return Details</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-surface-500 mb-1">Reason for Return *</label>
                        <select name="reason" required class="w-full px-4 py-3 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white">
                            <option value="">Select a reason...</option>
                            <option value="Defective or Damaged">Defective or Damaged</option>
                            <option value="Wrong Item Sent">Wrong Item Sent</option>
                            <option value="Customer Changed Mind">Customer Changed Mind</option>
                            <option value="Item Not as Described">Item Not as Described</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-surface-500 mb-1">Additional Notes</label>
                        <textarea name="notes" rows="2" class="w-full px-4 py-3 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white" placeholder="Any extra info regarding the return..."></textarea>
                    </div>
                </div>
            </div>

            {{-- Select Items --}}
            <div class="bg-white dark:bg-surface-800 p-6 rounded-2xl border border-surface-200 dark:border-surface-700 shadow-sm">
                <h3 class="text-sm font-bold text-surface-800 dark:text-white mb-4">Select Items to Return</h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-xs font-semibold text-surface-500 uppercase">
                                <th class="pb-3 pr-4 w-10">Return</th>
                                <th class="pb-3 pr-4">Product</th>
                                <th class="pb-3 pr-4 w-24">Qty to Return</th>
                                <th class="pb-3 w-40">Condition</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-100 dark:divide-surface-700" id="returnItems">
                            @foreach($order->items as $index => $item)
                            <tr>
                                <td class="py-3 pr-4">
                                    <input type="checkbox" name="items[{{ $index }}][selected]" value="1" class="w-5 h-5 rounded text-primary-600 border-surface-300 focus:ring-primary-500" onchange="toggleRow(this, {{ $index }})">
                                    <input type="hidden" name="items[{{ $index }}][order_item_id]" value="{{ $item->id }}" disabled>
                                </td>
                                <td class="py-3 pr-4">
                                    <p class="text-sm font-semibold text-surface-800 dark:text-white">{{ $item->product_name }}</p>
                                    <p class="text-xs text-surface-400">Paid: ${{ number_format($item->price, 2) }} / ea (Ordered: {{ $item->quantity }})</p>
                                </td>
                                <td class="py-3 pr-4">
                                    <input type="number" name="items[{{ $index }}][quantity]" min="1" max="{{ $item->quantity }}" value="1" disabled class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 disabled:opacity-50 dark:text-white">
                                </td>
                                <td class="py-3">
                                    <select name="items[{{ $index }}][condition]" disabled class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 disabled:opacity-50 dark:text-white">
                                        <option value="good">Good (Restockable)</option>
                                        <option value="damaged">Damaged (Do Not Restock)</option>
                                        <option value="defective">Defective (Do Not Restock)</option>
                                    </select>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button type="submit" class="px-8 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-primary-500/25 transition-all">
                    Submit Return Request
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function toggleRow(checkbox, index) {
            const row = checkbox.closest('tr');
            const inputs = row.querySelectorAll('input:not([type="checkbox"]), select');
            inputs.forEach(input => {
                input.disabled = !checkbox.checked;
            });
        }
    </script>
    @endpush
</x-layouts.admin>

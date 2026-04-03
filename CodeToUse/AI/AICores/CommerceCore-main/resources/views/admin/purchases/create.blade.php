<x-layouts.admin>
    <x-slot:header>Create Purchase Order</x-slot:header>

    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-display font-bold text-surface-800 dark:text-white">New Purchase Order</h2>
                <p class="text-sm text-surface-500 mt-1">Order stock from suppliers to replenish inventory</p>
            </div>
            <a href="{{ route('admin.purchases.index') }}" class="text-sm text-surface-500 hover:text-surface-700 dark:hover:text-surface-300 font-medium">← Back to POs</a>
        </div>

        <form action="{{ route('admin.purchases.store') }}" method="POST" id="purchaseForm" class="space-y-6">
            @csrf

            {{-- Supplier Info --}}
            <div class="bg-white dark:bg-surface-800 p-6 rounded-2xl border border-surface-200 dark:border-surface-700 shadow-sm">
                <h3 class="text-sm font-bold text-surface-800 dark:text-white mb-4">Supplier Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-surface-500 mb-1">Select Supplier *</label>
                        <select name="supplier_id" required class="w-full px-4 py-2.5 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white">
                            <option value="">Choose a supplier...</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }} {{ $supplier->company ? '('.$supplier->company.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-surface-500 mb-1">Payment Status</label>
                        <select name="payment_status" class="w-full px-4 py-2.5 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white">
                            <option value="unpaid">Unpaid</option>
                            <option value="partial">Partial</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-xs font-semibold text-surface-500 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-2.5 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white placeholder-surface-400" placeholder="Internal notes...">{{ old('notes') }}</textarea>
                </div>
            </div>

            {{-- Line Items --}}
            <div class="bg-white dark:bg-surface-800 p-6 rounded-2xl border border-surface-200 dark:border-surface-700 shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-bold text-surface-800 dark:text-white">Line Items</h3>
                    <button type="button" onclick="addItemRow()" class="inline-flex items-center gap-1 text-xs font-bold text-primary-600 hover:text-primary-700 dark:text-primary-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Item
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left" id="itemsTable">
                        <thead>
                            <tr class="text-xs font-semibold text-surface-500 uppercase">
                                <th class="pb-3 pr-4">Product *</th>
                                <th class="pb-3 pr-4 w-24">Qty *</th>
                                <th class="pb-3 pr-4 w-32">Unit Cost *</th>
                                <th class="pb-3 w-32">Subtotal</th>
                                <th class="pb-3 w-10"></th>
                            </tr>
                        </thead>
                        <tbody id="itemRows">
                            <tr class="item-row">
                                <td class="pr-4 pb-3">
                                    <select name="items[0][product_id]" required class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white">
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }} (Stock: {{ $product->stock }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="pr-4 pb-3">
                                    <input type="number" name="items[0][quantity]" min="1" required value="1" oninput="calcRow(this)" class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white">
                                </td>
                                <td class="pr-4 pb-3">
                                    <input type="number" step="0.01" name="items[0][unit_cost]" min="0.01" required oninput="calcRow(this)" class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white" placeholder="0.00">
                                </td>
                                <td class="pb-3">
                                    <span class="row-subtotal text-sm font-bold text-surface-800 dark:text-white">$0.00</span>
                                </td>
                                <td class="pb-3">
                                    <button type="button" onclick="removeRow(this)" class="p-1 text-surface-400 hover:text-red-500 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 pt-4 border-t border-surface-200 dark:border-surface-700 flex justify-between items-center">
                    <span class="text-[10px] font-black text-surface-400 uppercase tracking-widest">Grand Total</span>
                    <span id="grandTotal" class="text-xl font-bold text-surface-800 dark:text-white">$0.00</span>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.purchases.index') }}" class="px-6 py-2.5 text-sm font-semibold text-surface-600 dark:text-surface-400 bg-surface-100 dark:bg-surface-700 rounded-xl hover:bg-surface-200 dark:hover:bg-surface-600 transition-colors">Cancel</a>
                <button type="submit" class="px-8 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-primary-500/25 transition-all">
                    Create Purchase Order
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        let rowIndex = 1;
        const productsJson = @json($products);

        function addItemRow() {
            const tbody = document.getElementById('itemRows');
            let options = '<option value="">Select Product</option>';
            productsJson.forEach(p => {
                options += `<option value="${p.id}">${p.name} (Stock: ${p.stock})</option>`;
            });
            const row = document.createElement('tr');
            row.className = 'item-row';
            row.innerHTML = `
                <td class="pr-4 pb-3"><select name="items[${rowIndex}][product_id]" required class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white">${options}</select></td>
                <td class="pr-4 pb-3"><input type="number" name="items[${rowIndex}][quantity]" min="1" required value="1" oninput="calcRow(this)" class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white"></td>
                <td class="pr-4 pb-3"><input type="number" step="0.01" name="items[${rowIndex}][unit_cost]" min="0.01" required oninput="calcRow(this)" class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white" placeholder="0.00"></td>
                <td class="pb-3"><span class="row-subtotal text-sm font-bold text-surface-800 dark:text-white">$0.00</span></td>
                <td class="pb-3"><button type="button" onclick="removeRow(this)" class="p-1 text-surface-400 hover:text-red-500 rounded-lg transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></td>
            `;
            tbody.appendChild(row);
            rowIndex++;
        }

        function removeRow(btn) {
            const rows = document.querySelectorAll('.item-row');
            if (rows.length > 1) {
                btn.closest('tr').remove();
                updateGrandTotal();
            }
        }

        function calcRow(input) {
            const row = input.closest('tr');
            const qty = parseFloat(row.querySelector('[name$="[quantity]"]').value) || 0;
            const cost = parseFloat(row.querySelector('[name$="[unit_cost]"]').value) || 0;
            const sub = qty * cost;
            row.querySelector('.row-subtotal').textContent = '$' + sub.toFixed(2);
            updateGrandTotal();
        }

        function updateGrandTotal() {
            let total = 0;
            document.querySelectorAll('.row-subtotal').forEach(el => {
                total += parseFloat(el.textContent.replace('$', '')) || 0;
            });
            document.getElementById('grandTotal').textContent = '$' + total.toFixed(2);
        }
    </script>
    @endpush
</x-layouts.admin>

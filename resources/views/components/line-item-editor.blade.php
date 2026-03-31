@php use Illuminate\Support\Str; @endphp
@props([
    'items' => collect(),
    'name' => 'items',
    'title' => __('Line Items'),
    'addLabel' => __('Add Item'),
])

@php
    $seedItems = collect(old($name, $items))
        ->sortBy('sort_order')
        ->values()
        ->map(function ($item) {
            return [
                'key'         => $item['id'] ?? (string) Str::uuid(),
                'description' => $item['description'] ?? '',
                'quantity'    => (float) ($item['quantity'] ?? 1),
                'unit_price'  => (float) ($item['unit_price'] ?? 0),
                'tax_rate'    => (float) ($item['tax_rate'] ?? 0),
                'sort_order'  => (int) ($item['sort_order'] ?? 0),
            ];
        });
@endphp

<div class="mt-6" x-data="lineItemEditor(@json($seedItems), '{{ $name }}')">
    <div class="flex items-center justify-between mb-2">
        <div class="font-semibold">{{ $title }}</div>
        <x-button type="button" variant="ghost" x-on:click="add()">
            <x-tabler-plus class="size-4" /> {{ $addLabel }}
        </x-button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm border rounded-md">
            <thead>
                <tr class="bg-slate-50">
                    <th class="p-2 text-left">{{ __('Description') }}</th>
                    <th class="p-2 text-left w-24">{{ __('Qty') }}</th>
                    <th class="p-2 text-left w-28">{{ __('Unit Price') }}</th>
                    <th class="p-2 text-left w-24">{{ __('Tax %') }}</th>
                    <th class="p-2 text-left w-28">{{ __('Line Total') }}</th>
                    <th class="p-2 w-24">{{ __('Order') }}</th>
                    <th class="p-2 w-20"></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(item, index) in items" :key="item.key">
                    <tr>
                        <td class="p-2">
                            <input type="text" class="w-full form-input" x-model="item.description" :name="`${fieldName}[${index}][description]`">
                        </td>
                        <td class="p-2">
                            <input type="number" step="0.01" class="w-full form-input" x-model.number="item.quantity" :name="`${fieldName}[${index}][quantity]`">
                        </td>
                        <td class="p-2">
                            <input type="number" step="0.01" class="w-full form-input" x-model.number="item.unit_price" :name="`${fieldName}[${index}][unit_price]`">
                        </td>
                        <td class="p-2">
                            <input type="number" step="0.01" class="w-full form-input" x-model.number="item.tax_rate" :name="`${fieldName}[${index}][tax_rate]`">
                        </td>
                        <td class="p-2">
                            <input type="number" step="0.01" class="w-full form-input" :value="(item.quantity * item.unit_price).toFixed(2)" readonly>
                        </td>
                        <td class="p-2">
                            <input type="number" class="w-full form-input" x-model.number="item.sort_order" :name="`${fieldName}[${index}][sort_order]`">
                        </td>
                        <td class="p-2 text-right space-x-1">
                            <button type="button" class="text-xs text-slate-500" x-on:click="moveUp(index)">↑</button>
                            <button type="button" class="text-xs text-slate-500" x-on:click="moveDown(index)">↓</button>
                            <button type="button" class="text-xs text-rose-600" x-on:click="remove(index)">{{ __('Remove') }}</button>
                        </td>
                    </tr>
                </template>
                <tr x-show="items.length === 0">
                    <td colspan="7" class="p-3 text-center text-slate-500">{{ __('No items yet. Add one above.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@once
    @php
        $lineItemEditorScript = <<<'SCRIPT'
<script>
    window.lineItemEditor = function(seedItems, fieldName = 'items') {
        return {
            items: seedItems || [],
            fieldName,
            add() {
                this.items.push({
                    key: (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : 'item-'+Date.now()+Math.random(),
                    description: '',
                    quantity: 1,
                    unit_price: 0,
                    tax_rate: 0,
                    sort_order: this.items.length,
                });
            },
            remove(index) {
                this.items.splice(index, 1);
                this.reindex();
            },
            moveUp(index) {
                if (index === 0) return;
                [this.items[index - 1], this.items[index]] = [this.items[index], this.items[index - 1]];
                this.reindex();
            },
            moveDown(index) {
                if (index >= this.items.length - 1) return;
                [this.items[index + 1], this.items[index]] = [this.items[index], this.items[index + 1]];
                this.reindex();
            },
            reindex() {
                this.items = this.items.map((item, idx) => ({ ...item, sort_order: idx }));
            },
        };
    };
</script>
SCRIPT;
    @endphp

    @push('script')
        {!! $lineItemEditorScript !!}
    @endpush
@endonce

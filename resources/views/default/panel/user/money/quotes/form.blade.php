@extends('panel.layout.app')
@section('title', $quote->exists ? __('Edit Quote') : __('New Quote'))

@php use Illuminate\Support\Str; @endphp
@section('content')
    <div class="py-6 space-y-6 max-w-4xl">
        <x-card>
            <form method="post" action="{{ $quote->exists ? route('dashboard.money.quotes.update', $quote) : route('dashboard.money.quotes.store') }}">
                @csrf
                @if($quote->exists)
                    @method('PUT')
                @endif
                <div class="grid md:grid-cols-2 gap-4">
                    <x-input name="quote_number" label="{{ __('Quote Number') }}" value="{{ old('quote_number', $quote->quote_number) }}" required />
                    <x-input name="title" label="{{ __('Title') }}" value="{{ old('title', $quote->title) }}" />
                    <x-select name="customer_id" label="{{ __('Customer') }}">
                        <option value="">{{ __('Select') }}</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id', $quote->customer_id) == $customer->id)>{{ $customer->name }}</option>
                        @endforeach
                    </x-select>
                    <x-select name="site_id" label="{{ __('Site') }}">
                        <option value="">{{ __('Select') }}</option>
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}" @selected(old('site_id', $quote->site_id) == $site->id)>{{ $site->name }}</option>
                        @endforeach
                    </x-select>
                    <x-input name="issue_date" type="date" label="{{ __('Issue Date') }}" value="{{ old('issue_date', optional($quote->issue_date)->toDateString()) }}" />
                    <x-input name="valid_until" type="date" label="{{ __('Valid Until') }}" value="{{ old('valid_until', optional($quote->valid_until)->toDateString()) }}" />
                    <x-input name="currency" label="{{ __('Currency') }}" value="{{ old('currency', $quote->currency ?? 'USD') }}" />
                </div>
                <div class="mt-6" x-data="lineItems()">
                    <div class="flex items-center justify-between mb-2">
                        <div class="font-semibold">{{ __('Line Items') }}</div>
                        <x-button type="button" variant="ghost" x-on:click="add()">
                            <x-tabler-plus class="size-4" /> {{ __('Add Item') }}
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
                                            <input type="text" class="w-full form-input" x-model="item.description" :name="`items[${index}][description]`">
                                        </td>
                                        <td class="p-2">
                                            <input type="number" step="0.01" class="w-full form-input" x-model.number="item.quantity" :name="`items[${index}][quantity]`">
                                        </td>
                                        <td class="p-2">
                                            <input type="number" step="0.01" class="w-full form-input" x-model.number="item.unit_price" :name="`items[${index}][unit_price]`">
                                        </td>
                                        <td class="p-2">
                                            <input type="number" step="0.01" class="w-full form-input" x-model.number="item.tax_rate" :name="`items[${index}][tax_rate]`">
                                        </td>
                                        <td class="p-2">
                                            <input type="number" step="0.01" class="w-full form-input" :value="(item.quantity * item.unit_price).toFixed(2)" readonly>
                                        </td>
                                        <td class="p-2">
                                            <input type="number" class="w-full form-input" x-model.number="item.sort_order" :name="`items[${index}][sort_order]`">
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
                <div class="mt-4">
                    <x-textarea name="notes" label="{{ __('Notes') }}">{{ old('notes', $quote->notes) }}</x-textarea>
                </div>
                <div class="mt-4">
                    <x-textarea name="checklist_template_raw" label="{{ __('Checklist template (one item per line)') }}">{{ old('checklist_template_raw', $quote->checklist_template ? implode(PHP_EOL, $quote->checklist_template) : '') }}</x-textarea>
                    <p class="text-xs text-slate-500 mt-1">{{ __('These items will become checklist tasks on conversion.') }}</p>
                </div>
                <div class="mt-6 flex gap-3">
                    <x-button type="submit">
                        <x-tabler-check class="size-4" />
                        {{ $quote->exists ? __('Save') : __('Create') }}
                    </x-button>
                    <x-button variant="ghost" href="{{ $quote->exists ? route('dashboard.money.quotes.show', $quote) : route('dashboard.money.quotes.index') }}">
                        {{ __('Cancel') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
    <script>
        document.addEventListener('alpine:init', () => {
            const seedItems = @json(
                collect(old('items', $quote->items->sortBy('sort_order')->values()->toArray()))->map(function($item){
                    return [
                        'key' => $item['id'] ?? (string) Str::uuid(),
                        'description' => $item['description'] ?? '',
                        'quantity' => (float) ($item['quantity'] ?? 1),
                        'unit_price' => (float) ($item['unit_price'] ?? 0),
                        'tax_rate' => (float) ($item['tax_rate'] ?? 0),
                        'sort_order' => (int) ($item['sort_order'] ?? 0),
                    ];
                })
            );
            Alpine.data('lineItems', () => ({
                items: seedItems,
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
            }));
        });
    </script>
@endsection

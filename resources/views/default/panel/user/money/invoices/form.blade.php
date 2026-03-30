@extends('panel.layout.app')
@section('title', $invoice->exists ? __('Edit Invoice') : __('New Invoice'))

@php use Illuminate\Support\Str; @endphp
@section('content')
    <div class="py-6 space-y-6 max-w-4xl">
        <x-card>
            <form method="post" action="{{ $invoice->exists ? route('dashboard.money.invoices.update', $invoice) : route('dashboard.money.invoices.store') }}">
                @csrf
                @if($invoice->exists)
                    @method('PUT')
                @endif
                <div class="grid md:grid-cols-2 gap-4">
                    <x-input name="invoice_number" label="{{ __('Invoice Number') }}" value="{{ old('invoice_number', $invoice->invoice_number) }}" required />
                    <x-input name="title" label="{{ __('Title') }}" value="{{ old('title', $invoice->title) }}" />
                    <x-select name="customer_id" label="{{ __('Customer') }}">
                        <option value="">{{ __('Select') }}</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id', $invoice->customer_id) == $customer->id)>{{ $customer->name }}</option>
                        @endforeach
                    </x-select>
                    <x-select name="quote_id" label="{{ __('Quote') }}">
                        <option value="">{{ __('Select') }}</option>
                        @foreach($quotes as $quote)
                            <option value="{{ $quote->id }}" @selected(old('quote_id', $invoice->quote_id) == $quote->id)>{{ $quote->quote_number }}</option>
                        @endforeach
                    </x-select>
                    <x-input name="issue_date" type="date" label="{{ __('Issue Date') }}" value="{{ old('issue_date', optional($invoice->issue_date)->toDateString()) }}" />
                    <x-input name="due_date" type="date" label="{{ __('Due Date') }}" value="{{ old('due_date', optional($invoice->due_date)->toDateString()) }}" />
                    <x-input name="currency" label="{{ __('Currency') }}" value="{{ old('currency', $invoice->currency ?? 'USD') }}" />
                    <x-select name="status" label="{{ __('Status') }}">
                        @foreach(['draft','issued','partial','paid','overdue','void'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $invoice->status ?? 'draft') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </x-select>
                </div>
                <div class="mt-4">
                    <x-textarea name="notes" label="{{ __('Notes') }}">{{ old('notes', $invoice->notes) }}</x-textarea>
                </div>
                <div class="mt-6" x-data="invoiceItems()">
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
                <div class="mt-6 flex gap-3">
                    <x-button type="submit">
                        <x-tabler-check class="size-4" />
                        {{ $invoice->exists ? __('Save') : __('Create') }}
                    </x-button>
                    <x-button variant="ghost" href="{{ $invoice->exists ? route('dashboard.money.invoices.show', $invoice) : route('dashboard.money.invoices.index') }}">
                        {{ __('Cancel') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
    <script>
        document.addEventListener('alpine:init', () => {
            const seedItems = @json(
                collect(old('items', $invoice->items->sortBy('sort_order')->values()->toArray()))->map(function($item){
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
            Alpine.data('invoiceItems', () => ({
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

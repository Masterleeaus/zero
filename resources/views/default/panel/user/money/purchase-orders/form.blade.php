@extends('panel.layout.app')
@section('title', $purchaseOrder->exists ? __('Edit Purchase Order') : __('New Purchase Order'))

@section('content')
    <div class="py-6 max-w-2xl space-y-4">
        <x-card>
            <form method="post"
                  action="{{ $purchaseOrder->exists ? route('dashboard.money.purchase-orders.update', $purchaseOrder) : route('dashboard.money.purchase-orders.store') }}">
                @csrf
                @if($purchaseOrder->exists)
                    @method('PUT')
                @endif

                <div class="grid md:grid-cols-2 gap-4">
                    <x-input name="po_number" label="{{ __('PO Number') }}" value="{{ old('po_number', $purchaseOrder->po_number) }}" required />

                    <x-select name="supplier_id" label="{{ __('Supplier') }}" required>
                        <option value="">{{ __('Select supplier') }}</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id', $purchaseOrder->supplier_id) == $supplier->id)>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </x-select>

                    <x-input name="order_date" type="date" label="{{ __('Order Date') }}"
                             value="{{ old('order_date', optional($purchaseOrder->order_date)->toDateString()) }}" />
                    <x-input name="expected_date" type="date" label="{{ __('Expected Date') }}"
                             value="{{ old('expected_date', optional($purchaseOrder->expected_date)->toDateString()) }}" />
                    <x-input name="currency_code" label="{{ __('Currency') }}"
                             value="{{ old('currency_code', $purchaseOrder->currency_code ?? 'AUD') }}" />
                    <x-input name="reference" label="{{ __('Reference') }}"
                             value="{{ old('reference', $purchaseOrder->reference) }}" />
                    <x-input name="service_job_id" type="number" label="{{ __('Job ID (optional)') }}"
                             value="{{ old('service_job_id', $purchaseOrder->service_job_id) }}" />
                    <x-select name="status" label="{{ __('Status') }}">
                        @foreach(['draft','sent','partial','received','cancelled'] as $opt)
                            <option value="{{ $opt }}" @selected(old('status', $purchaseOrder->status ?? 'draft') === $opt)>{{ ucfirst($opt) }}</option>
                        @endforeach
                    </x-select>
                    <x-input name="subtotal" type="number" step="0.01" label="{{ __('Subtotal') }}"
                             value="{{ old('subtotal', $purchaseOrder->subtotal ?? 0) }}" />
                    <x-input name="tax_amount" type="number" step="0.01" label="{{ __('Tax Amount') }}"
                             value="{{ old('tax_amount', $purchaseOrder->tax_amount ?? 0) }}" />
                    <x-input name="total_amount" type="number" step="0.01" label="{{ __('Total Amount') }}"
                             value="{{ old('total_amount', $purchaseOrder->total_amount ?? 0) }}" />
                </div>

                <div class="mt-4">
                    <x-textarea name="notes" label="{{ __('Notes') }}">{{ old('notes', $purchaseOrder->notes) }}</x-textarea>
                </div>

                <div class="mt-6 flex gap-3">
                    <x-button type="submit">
                        <x-tabler-check class="size-4" />
                        {{ $purchaseOrder->exists ? __('Save') : __('Create') }}
                    </x-button>
                    <x-button variant="ghost"
                              href="{{ $purchaseOrder->exists ? route('dashboard.money.purchase-orders.show', $purchaseOrder) : route('dashboard.money.purchase-orders.index') }}">
                        {{ __('Cancel') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection

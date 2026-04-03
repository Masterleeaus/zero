@extends('panel.layout.app')
@section('title', $bill->exists ? __('Edit Supplier Bill') : __('New Supplier Bill'))

@section('content')
    <div class="py-6 max-w-4xl space-y-4">
        <x-card>
            <form method="post"
                  action="{{ $bill->exists ? route('dashboard.money.supplier-bills.update', $bill) : route('dashboard.money.supplier-bills.store') }}">
                @csrf
                @if($bill->exists)
                    @method('PUT')
                @endif

                <div class="grid md:grid-cols-2 gap-4">
                    <x-select name="supplier_id" label="{{ __('Supplier') }}" required>
                        <option value="">{{ __('Select supplier') }}</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id', $bill->supplier_id ?? request('supplier_id')) == $supplier->id)>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </x-select>

                    <x-select name="purchase_order_id" label="{{ __('Purchase Order (optional)') }}">
                        <option value="">{{ __('None') }}</option>
                        @foreach($pos as $po)
                            <option value="{{ $po->id }}" @selected(old('purchase_order_id', $bill->purchase_order_id ?? request('purchase_order_id')) == $po->id)>
                                {{ $po->po_number }}
                            </option>
                        @endforeach
                    </x-select>

                    <x-input name="reference" label="{{ __('Reference') }}" value="{{ old('reference', $bill->reference) }}" />
                    <x-input name="currency" label="{{ __('Currency') }}" value="{{ old('currency', $bill->currency ?? 'AUD') }}" />
                    <x-input name="bill_date" type="date" label="{{ __('Bill Date') }}" value="{{ old('bill_date', optional($bill->bill_date)->toDateString() ?? now()->toDateString()) }}" required />
                    <x-input name="due_date" type="date" label="{{ __('Due Date') }}" value="{{ old('due_date', optional($bill->due_date)->toDateString()) }}" />

                    <x-select name="status" label="{{ __('Status') }}">
                        @foreach(['draft','awaiting_payment','partial','paid','overdue','void'] as $opt)
                            <option value="{{ $opt }}" @selected(old('status', $bill->status ?? 'draft') === $opt)>{{ ucfirst(str_replace('_', ' ', $opt)) }}</option>
                        @endforeach
                    </x-select>

                    <x-input name="subtotal" type="number" step="0.01" label="{{ __('Subtotal') }}" value="{{ old('subtotal', $bill->subtotal ?? 0) }}" />
                    <x-input name="tax_total" type="number" step="0.01" label="{{ __('Tax Total') }}" value="{{ old('tax_total', $bill->tax_total ?? 0) }}" />
                    <x-input name="total" type="number" step="0.01" label="{{ __('Total') }}" value="{{ old('total', $bill->total ?? 0) }}" />
                </div>

                <div class="mt-4">
                    <x-textarea name="notes" label="{{ __('Notes') }}">{{ old('notes', $bill->notes) }}</x-textarea>
                </div>

                {{-- Line Items --}}
                <div class="mt-6">
                    <h3 class="font-semibold mb-3">{{ __('Line Items') }}</h3>
                    <div id="bill-lines" class="space-y-2">
                        @foreach(old('lines', $bill->exists ? $bill->lines->toArray() : [[]]) as $i => $line)
                            <div class="grid md:grid-cols-5 gap-2 items-end">
                                <x-select name="lines[{{ $i }}][account_id]" label="{{ $loop->first ? __('Account') : '' }}">
                                    <option value="">{{ __('None') }}</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" @selected(($line['account_id'] ?? null) == $account->id)>
                                            {{ $account->code }} — {{ $account->name }}
                                        </option>
                                    @endforeach
                                </x-select>
                                <x-input name="lines[{{ $i }}][description]"
                                         label="{{ $loop->first ? __('Description') : '' }}"
                                         value="{{ $line['description'] ?? '' }}" />
                                <x-input name="lines[{{ $i }}][amount]" type="number" step="0.01"
                                         label="{{ $loop->first ? __('Amount') : '' }}"
                                         value="{{ $line['amount'] ?? 0 }}" required />
                                <x-input name="lines[{{ $i }}][tax_rate]" type="number" step="0.01"
                                         label="{{ $loop->first ? __('Tax %') : '' }}"
                                         value="{{ $line['tax_rate'] ?? 0 }}" />
                                <x-input name="lines[{{ $i }}][service_job_id]" type="number"
                                         label="{{ $loop->first ? __('Job ID') : '' }}"
                                         value="{{ $line['service_job_id'] ?? '' }}" />
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <x-button type="submit">
                        <x-tabler-check class="size-4" />
                        {{ $bill->exists ? __('Save') : __('Create') }}
                    </x-button>
                    <x-button variant="ghost"
                              href="{{ $bill->exists ? route('dashboard.money.supplier-bills.show', $bill) : route('dashboard.money.supplier-bills.index') }}">
                        {{ __('Cancel') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection

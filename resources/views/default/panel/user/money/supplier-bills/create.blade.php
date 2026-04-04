@extends('panel.layout.app')
@section('title', __('New Supplier Bill'))

@section('content')
    <div class="py-6 max-w-3xl">
        <h1 class="text-xl font-semibold mb-4">{{ __('New Supplier Bill') }}</h1>

        <form method="post" action="{{ route('dashboard.money.supplier-bills.store') }}" class="space-y-4">
            @csrf

            <div class="grid md:grid-cols-2 gap-4">
                <x-form.group>
                    <x-form.label for="supplier_id">{{ __('Supplier') }} <span class="text-red-500">*</span></x-form.label>
                    <x-form.select id="supplier_id" name="supplier_id" required>
                        <option value="">{{ __('Select supplier…') }}</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </x-form.select>
                    <x-form.error field="supplier_id" />
                </x-form.group>

                <x-form.group>
                    <x-form.label for="reference">{{ __('Reference') }}</x-form.label>
                    <x-form.input id="reference" name="reference" value="{{ old('reference') }}" />
                    <x-form.error field="reference" />
                </x-form.group>

                <x-form.group>
                    <x-form.label for="bill_date">{{ __('Bill Date') }} <span class="text-red-500">*</span></x-form.label>
                    <x-form.input type="date" id="bill_date" name="bill_date" value="{{ old('bill_date', now()->toDateString()) }}" required />
                    <x-form.error field="bill_date" />
                </x-form.group>

                <x-form.group>
                    <x-form.label for="due_date">{{ __('Due Date') }} <span class="text-red-500">*</span></x-form.label>
                    <x-form.input type="date" id="due_date" name="due_date" value="{{ old('due_date', now()->addDays(30)->toDateString()) }}" required />
                    <x-form.error field="due_date" />
                </x-form.group>
            </div>

            <x-form.group>
                <x-form.label for="notes">{{ __('Notes') }}</x-form.label>
                <x-form.textarea id="notes" name="notes">{{ old('notes') }}</x-form.textarea>
            </x-form.group>

            <div>
                <h2 class="text-lg font-medium mb-2">{{ __('Line Items') }}</h2>
                <div id="bill-items" class="space-y-2">
                    <div class="grid grid-cols-12 gap-2 items-end bill-item">
                        <div class="col-span-5">
                            <x-form.label>{{ __('Description') }}</x-form.label>
                            <x-form.input name="items[0][description]" required />
                        </div>
                        <div class="col-span-2">
                            <x-form.label>{{ __('Qty') }}</x-form.label>
                            <x-form.input type="number" name="items[0][quantity]" value="1" min="0.01" step="0.01" />
                        </div>
                        <div class="col-span-2">
                            <x-form.label>{{ __('Unit Price') }}</x-form.label>
                            <x-form.input type="number" name="items[0][unit_price]" value="0" min="0" step="0.01" />
                        </div>
                        <div class="col-span-3">
                            <x-form.label>{{ __('Account') }}</x-form.label>
                            <x-form.select name="items[0][account_id]">
                                <option value="">{{ __('None') }}</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->code ? "[{$account->code}] " : '' }}{{ $account->name }}</option>
                                @endforeach
                            </x-form.select>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="addBillLine()" class="mt-2 text-sm text-blue-600 hover:underline">+ {{ __('Add line') }}</button>
            </div>

            <div class="flex gap-2">
                <x-button type="submit">{{ __('Create Bill') }}</x-button>
                <x-button variant="secondary" href="{{ route('dashboard.money.supplier-bills.index') }}">{{ __('Cancel') }}</x-button>
            </div>
        </form>
    </div>

    <script>
    let lineIdx = 1;
    function addBillLine() {
        const container = document.getElementById('bill-items');
        const tmpl = container.querySelector('.bill-item').cloneNode(true);
        tmpl.querySelectorAll('input,select').forEach(el => {
            el.name = el.name.replace(/\[\d+\]/, '[' + lineIdx + ']');
            if (el.type === 'number') el.value = el.defaultValue;
            else el.value = '';
        });
        container.appendChild(tmpl);
        lineIdx++;
    }
    </script>
@endsection

@extends('panel.layout.app')
@section('title', $invoice->exists ? __('Edit Invoice') : __('New Invoice'))

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
                <x-line-item-editor :items="$invoice->items" name="items" />
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
@endsection

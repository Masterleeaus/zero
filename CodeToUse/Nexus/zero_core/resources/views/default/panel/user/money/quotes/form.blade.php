@extends('panel.layout.app')
@section('title', $quote->exists ? __('Edit Quote') : __('New Quote'))

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
                <x-line-item-editor :items="$quote->items" name="items" />
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
@endsection

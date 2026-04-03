@extends('panel.layout.app')
@section('title', $supplier->exists ? __('Edit Supplier') : __('New Supplier'))

@section('content')
    <div class="py-6 max-w-2xl space-y-4">
        <x-card>
            <form method="post"
                  action="{{ $supplier->exists ? route('dashboard.money.suppliers.update', $supplier) : route('dashboard.money.suppliers.store') }}">
                @csrf
                @if($supplier->exists)
                    @method('PUT')
                @endif

                <div class="grid md:grid-cols-2 gap-4">
                    <x-input name="name" label="{{ __('Name') }}" value="{{ old('name', $supplier->name) }}" required />
                    <x-input name="email" type="email" label="{{ __('Email') }}" value="{{ old('email', $supplier->email) }}" />
                    <x-input name="phone" label="{{ __('Phone') }}" value="{{ old('phone', $supplier->phone) }}" />
                    <x-input name="tax_number" label="{{ __('Tax Number') }}" value="{{ old('tax_number', $supplier->tax_number) }}" />
                    <x-input name="payment_terms" label="{{ __('Payment Terms') }}" value="{{ old('payment_terms', $supplier->payment_terms) }}" />
                    <x-input name="currency_code" label="{{ __('Currency') }}" value="{{ old('currency_code', $supplier->currency_code ?? 'AUD') }}" />
                    <x-input name="city" label="{{ __('City') }}" value="{{ old('city', $supplier->city) }}" />
                    <x-input name="country" label="{{ __('Country') }}" value="{{ old('country', $supplier->country) }}" />
                    <x-select name="status" label="{{ __('Status') }}">
                        @foreach(['active', 'inactive'] as $opt)
                            <option value="{{ $opt }}" @selected(old('status', $supplier->status ?? 'active') === $opt)>{{ ucfirst($opt) }}</option>
                        @endforeach
                    </x-select>
                </div>

                <div class="mt-4">
                    <x-textarea name="address" label="{{ __('Address') }}">{{ old('address', $supplier->address) }}</x-textarea>
                </div>
                <div class="mt-4">
                    <x-textarea name="notes" label="{{ __('Notes') }}">{{ old('notes', $supplier->notes) }}</x-textarea>
                </div>

                <div class="mt-6 flex gap-3">
                    <x-button type="submit">
                        <x-tabler-check class="size-4" />
                        {{ $supplier->exists ? __('Save') : __('Create') }}
                    </x-button>
                    <x-button variant="ghost"
                              href="{{ $supplier->exists ? route('dashboard.money.suppliers.show', $supplier) : route('dashboard.money.suppliers.index') }}">
                        {{ __('Cancel') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection

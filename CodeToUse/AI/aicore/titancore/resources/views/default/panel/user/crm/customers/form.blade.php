@extends('panel.layout.app')
@section('title', $customer->exists ? __('Edit Customer') : __('New Customer'))

@section('content')
    <div class="py-6">
        <form method="post"
              action="{{ $customer->exists ? route('dashboard.crm.customers.update', $customer) : route('dashboard.crm.customers.store') }}"
              class="space-y-4">
            @csrf
            @if($customer->exists)
                @method('put')
            @endif

            <div class="grid md:grid-cols-2 gap-4">
                <x-input label="{{ __('Name') }}" name="name" required value="{{ old('name', $customer->name) }}" />
                <x-input label="{{ __('Email') }}" type="email" name="email" value="{{ old('email', $customer->email) }}" />
                <x-input label="{{ __('Phone') }}" name="phone" value="{{ old('phone', $customer->phone) }}" />
                <x-input label="{{ __('Status') }}" name="status" value="{{ old('status', $customer->status) }}" />
            </div>

            <x-textarea label="{{ __('Notes') }}" name="notes" rows="4">{{ old('notes', $customer->notes) }}</x-textarea>

            <div class="flex gap-3">
                <x-button type="submit">
                    <x-tabler-check class="size-4" />
                    {{ $customer->exists ? __('Update') : __('Create') }}
                </x-button>
                <x-button type="button" href="{{ $customer->exists ? route('dashboard.crm.customers.show', $customer) : route('dashboard.crm.customers.index') }}" variant="secondary">
                    {{ __('Cancel') }}
                </x-button>
            </div>
        </form>
    </div>
@endsection

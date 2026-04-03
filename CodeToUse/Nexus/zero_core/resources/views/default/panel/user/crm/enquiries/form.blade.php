@extends('panel.layout.app')
@section('title', $enquiry->exists ? __('Edit Enquiry') : __('New Enquiry'))

@section('content')
    <div class="py-6">
        <form method="post"
              action="{{ $enquiry->exists ? route('dashboard.crm.enquiries.update', $enquiry) : route('dashboard.crm.enquiries.store') }}"
              class="space-y-4">
            @csrf
            @if($enquiry->exists)
                @method('put')
            @endif

            <div class="grid md:grid-cols-2 gap-4">
                <x-input label="{{ __('Name') }}" name="name" required value="{{ old('name', $enquiry->name) }}" />
                <x-input label="{{ __('Email') }}" type="email" name="email" value="{{ old('email', $enquiry->email) }}" />
                <x-input label="{{ __('Phone') }}" name="phone" value="{{ old('phone', $enquiry->phone) }}" />
                <x-input label="{{ __('Status') }}" name="status" value="{{ old('status', $enquiry->status) }}" />
                <x-input label="{{ __('Source') }}" name="source" value="{{ old('source', $enquiry->source) }}" />

                <div>
                    <label class="form-label">{{ __('Customer') }}</label>
                    <x-select name="customer_id">
                        <option value="">{{ __('Unassigned') }}</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id', $enquiry->customer_id) == $customer->id)>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </x-select>
                </div>
            </div>

            <x-textarea label="{{ __('Notes') }}" name="notes" rows="4">{{ old('notes', $enquiry->notes) }}</x-textarea>

            <div class="flex gap-3">
                <x-button type="submit">
                    <x-tabler-check class="size-4" />
                    {{ $enquiry->exists ? __('Update') : __('Create') }}
                </x-button>
                <x-button type="button" href="{{ $enquiry->exists ? route('dashboard.crm.enquiries.show', $enquiry) : route('dashboard.crm.enquiries.index') }}" variant="secondary">
                    {{ __('Cancel') }}
                </x-button>
            </div>
        </form>
    </div>
@endsection

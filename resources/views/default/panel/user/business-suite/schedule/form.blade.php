@extends('default.panel.layout.app')

@section('title', __('Add Schedule Item'))

@section('content')
<div class="flex flex-col gap-y-6 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-heading">{{ __('Add Schedule Item') }}</h1>
    </div>

    <form method="POST" action="{{ route('dashboard.user.social-media.calendar') }}">
        @csrf

        <div class="mb-4">
            <label for="item_type" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Item Type') }}</label>
            <select
                id="item_type"
                name="item_type"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                required
            >
                <option value="">{{ __('— Select item type —') }}</option>
                <option value="quote_followup">{{ __('Quote Follow-Up') }}</option>
                <option value="booking">{{ __('Booking') }}</option>
                <option value="job">{{ __('Job Time') }}</option>
                <option value="invoice_reminder">{{ __('Invoice Reminder') }}</option>
                <option value="program_checkpoint">{{ __('Program Checkpoint') }}</option>
            </select>
        </div>

        <div class="mb-4">
            <label for="linked_contact" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Linked Contact') }}</label>
            <input
                type="text"
                id="linked_contact"
                name="linked_contact"
                placeholder="{{ __('Contact name or ID') }}"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
            />
        </div>

        <div class="mb-4">
            <label for="scheduled_at" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Date / Time') }}</label>
            <input
                type="datetime-local"
                id="scheduled_at"
                name="scheduled_at"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                required
            />
        </div>

        <div class="mb-6">
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Notes') }}</label>
            <textarea
                id="notes"
                name="notes"
                rows="3"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
            ></textarea>
        </div>

        <div class="flex gap-x-3">
            <button type="submit" class="lqd-btn lqd-btn-primary">{{ __('Save Schedule Item') }}</button>
            <a href="{{ route('dashboard.user.social-media.calendar') }}" class="lqd-btn lqd-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
@endsection

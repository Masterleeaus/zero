@extends('default.panel.layout.app')

@section('title', __('New Work Draft'))

@section('content')
<div class="flex flex-col gap-y-6 p-6" x-data="{ draftType: '' }">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-heading">{{ __('New Work Draft') }}</h1>
    </div>

    <form method="POST" action="{{ route('dashboard.user.social-media.post.index') }}">
        @csrf

        {{-- Draft type selector --}}
        <div class="mb-6">
            <label for="draft_type" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('Draft Type') }}
            </label>
            <select
                id="draft_type"
                name="draft_type"
                x-model="draftType"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                required
            >
                <option value="">{{ __('— Select draft type —') }}</option>
                <option value="booking">{{ __('Booking') }}</option>
                <option value="quote">{{ __('Quote') }}</option>
                <option value="service_job">{{ __('Service Job') }}</option>
                <option value="invoice">{{ __('Invoice') }}</option>
                <option value="report">{{ __('Report') }}</option>
            </select>
        </div>

        {{-- Common fields --}}
        <div class="mb-4">
            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Title') }}</label>
            <input
                type="text"
                id="title"
                name="title"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                required
            />
        </div>

        {{-- Booking / Service Job fields --}}
        <div x-show="draftType === 'booking' || draftType === 'service_job'" class="mb-4">
            <label for="scheduled_at" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Scheduled At') }}</label>
            <input
                type="datetime-local"
                id="scheduled_at"
                name="scheduled_at"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
            />
        </div>

        {{-- Quote / Invoice / Booking fields --}}
        <div x-show="draftType === 'quote' || draftType === 'invoice' || draftType === 'booking'" class="mb-4">
            <label for="contact_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Contact') }}</label>
            <input
                type="number"
                id="contact_id"
                name="contact_id"
                placeholder="{{ __('Contact ID') }}"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
            />
        </div>

        <div x-show="draftType === 'quote' || draftType === 'invoice'" class="mb-4">
            <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Currency') }}</label>
            <input
                type="text"
                id="currency"
                name="currency"
                value="AUD"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
            />
        </div>

        {{-- Notes (common) --}}
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
            <button type="submit" class="lqd-btn lqd-btn-primary">
                {{ __('Save Draft') }}
            </button>
            <a href="{{ route('dashboard.user.social-media.post.index') }}" class="lqd-btn lqd-btn-secondary">
                {{ __('Cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection

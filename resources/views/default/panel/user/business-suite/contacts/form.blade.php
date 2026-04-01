@extends('default.panel.layout.app')

@section('title', __('Add New Contact'))

@section('content')
<div class="flex flex-col gap-y-6 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-heading">{{ __('Add New Contact') }}</h1>
    </div>

    <form method="POST" action="{{ route('dashboard.user.social-media.platforms') }}">
        @csrf

        <div class="mb-4">
            <label for="client_name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Client Name') }}</label>
            <input
                type="text"
                id="client_name"
                name="client_name"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                required
            />
        </div>

        <div class="mb-4">
            <label for="channel" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Channel') }}</label>
            <select
                id="channel"
                name="channel"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
            >
                <option value="phone">{{ __('Phone') }}</option>
                <option value="web_portal">{{ __('Web Portal') }}</option>
                <option value="email">{{ __('Email') }}</option>
                <option value="walk_in">{{ __('Walk-In') }}</option>
            </select>
        </div>

        <div class="mb-4">
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Status') }}</label>
            <select
                id="status"
                name="status"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
            >
                <option value="active" title="{{ __('Active client') }}">{{ __('Active') }}</option>
                <option value="passive" title="{{ __('Passive lead') }}">{{ __('Passive') }}</option>
            </select>
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
            <button type="submit" class="lqd-btn lqd-btn-primary">{{ __('Save Contact') }}</button>
            <a href="{{ route('dashboard.user.social-media.platforms') }}" class="lqd-btn lqd-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
@endsection

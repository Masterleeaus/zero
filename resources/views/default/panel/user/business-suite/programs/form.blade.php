@extends('default.panel.layout.app')

@section('title', __('New Program'))

@section('content')
<div class="flex flex-col gap-y-6 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-heading">{{ __('New Program') }}</h1>
    </div>

    <form method="POST" action="{{ route('dashboard.user.social-media.campaign.index') }}">
        @csrf

        <div class="mb-4">
            <label for="program_name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Program Name') }}</label>
            <input
                type="text"
                id="program_name"
                name="program_name"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                required
            />
        </div>

        <div class="mb-4">
            <label for="program_type" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Program Type') }}</label>
            <input
                type="text"
                id="program_type"
                name="program_type"
                placeholder="{{ __('e.g. Maintenance, Inspection, Follow-up') }}"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
            />
        </div>

        <div class="mb-4">
            <label for="client_group" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Client Group') }}</label>
            <input
                type="text"
                id="client_group"
                name="client_group"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
            />
        </div>

        <div class="mb-4">
            <label for="service_cadence" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Service Cadence') }}</label>
            <select
                id="service_cadence"
                name="service_cadence"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
            >
                <option value="weekly">{{ __('Weekly') }}</option>
                <option value="fortnightly">{{ __('Fortnightly') }}</option>
                <option value="monthly">{{ __('Monthly') }}</option>
                <option value="custom">{{ __('Custom') }}</option>
            </select>
        </div>

        <div class="mb-6">
            <label for="work_draft_template" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Work Draft Template') }}</label>
            <textarea
                id="work_draft_template"
                name="work_draft_template"
                rows="3"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
            ></textarea>
        </div>

        <div class="flex gap-x-3">
            <button type="submit" class="lqd-btn lqd-btn-primary">{{ __('Save Program') }}</button>
            <a href="{{ route('dashboard.user.social-media.campaign.index') }}" class="lqd-btn lqd-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
@endsection

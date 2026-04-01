@extends('default.panel.layout.app')

@section('title', __('Contact'))

@section('content')
<div class="flex flex-col gap-y-6 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-heading">{{ __('Contact') }}</h1>
        <a href="{{ route('dashboard.user.social-media.platforms') }}" class="lqd-btn lqd-btn-secondary">
            {{ __('Back to Contacts') }}
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="rounded-lg border border-gray-200 p-4">
            <h2 class="text-base font-semibold mb-2">{{ __('Client Name') }}</h2>
            <p class="text-gray-500 text-sm">{{ __('Contact details will appear here.') }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <h2 class="text-base font-semibold mb-2">{{ __('Active Jobs') }}</h2>
            <p class="text-gray-500 text-sm">{{ __('0 active jobs') }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <h2 class="text-base font-semibold mb-2">{{ __('Outstanding Quotes') }}</h2>
            <p class="text-gray-500 text-sm">{{ __('0 outstanding quotes') }}</p>
        </div>
    </div>
</div>
@endsection

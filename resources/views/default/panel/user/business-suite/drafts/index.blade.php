@extends('default.panel.layout.app')

@section('title', __('Work Drafts'))

@section('content')
<div class="flex flex-col gap-y-6 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-heading">{{ __('Work Drafts') }}</h1>
        <a href="{{ route('dashboard.user.social-media.post.index') }}" class="lqd-btn lqd-btn-primary">
            {{ __('New Work Draft') }}
        </a>
    </div>

    <div class="flex gap-x-2 border-b border-gray-200 pb-2">
        <a href="?type=all" class="px-3 py-1 rounded text-sm {{ request('type', 'all') === 'all' ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100' }}">{{ __('All') }}</a>
        <a href="?type=booking" class="px-3 py-1 rounded text-sm {{ request('type') === 'booking' ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100' }}">{{ __('Bookings') }}</a>
        <a href="?type=quote" class="px-3 py-1 rounded text-sm {{ request('type') === 'quote' ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100' }}">{{ __('Quotes') }}</a>
        <a href="?type=service_job" class="px-3 py-1 rounded text-sm {{ request('type') === 'service_job' ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100' }}">{{ __('Jobs') }}</a>
        <a href="?type=invoice" class="px-3 py-1 rounded text-sm {{ request('type') === 'invoice' ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100' }}">{{ __('Invoices') }}</a>
        <a href="?type=report" class="px-3 py-1 rounded text-sm {{ request('type') === 'report' ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100' }}">{{ __('Reports') }}</a>
    </div>

    <p class="text-gray-500 text-sm">{{ __('Work drafts appear here. Create a new draft to get started.') }}</p>
</div>
@endsection

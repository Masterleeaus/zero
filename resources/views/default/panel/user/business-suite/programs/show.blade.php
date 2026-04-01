@extends('default.panel.layout.app')

@section('title', __('Program'))

@section('content')
<div class="flex flex-col gap-y-6 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-heading">{{ __('Program') }}</h1>
        <a href="{{ route('dashboard.user.social-media.campaign.index') }}" class="lqd-btn lqd-btn-secondary">
            {{ __('Back to Programs') }}
        </a>
    </div>
    <p class="text-gray-500 text-sm">{{ __('Program details will appear here.') }}</p>
</div>
@endsection

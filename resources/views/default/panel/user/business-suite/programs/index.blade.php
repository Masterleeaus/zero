@extends('default.panel.layout.app')

@section('title', __('Programs'))

@section('content')
<div class="flex flex-col gap-y-6 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-heading">{{ __('Programs') }}</h1>
        <a href="{{ route('dashboard.user.social-media.campaign.index') }}" class="lqd-btn lqd-btn-primary">
            {{ __('New Program') }}
        </a>
    </div>
    <p class="text-gray-500 text-sm">{{ __('Recurring service programs and workflows appear here.') }}</p>
</div>
@endsection

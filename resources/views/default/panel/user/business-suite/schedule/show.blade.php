@extends('default.panel.layout.app')

@section('title', __('Schedule Item'))

@section('content')
<div class="flex flex-col gap-y-6 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-heading">{{ __('Schedule Item') }}</h1>
        <a href="{{ route('dashboard.user.social-media.calendar') }}" class="lqd-btn lqd-btn-secondary">
            {{ __('Back to Master Schedule') }}
        </a>
    </div>
    <p class="text-gray-500 text-sm">{{ __('Schedule item details will appear here.') }}</p>
</div>
@endsection

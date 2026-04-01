@extends('default.panel.layout.app')

@section('title', __('Contacts'))

@section('content')
<div class="flex flex-col gap-y-6 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-heading">{{ __('Contacts') }}</h1>
        <a href="{{ route('dashboard.user.social-media.platforms') }}" class="lqd-btn lqd-btn-primary">
            {{ __('Add New Contact') }}
        </a>
    </div>
    <p class="text-gray-500 text-sm">{{ __('Client contacts appear here. Add a new contact to get started.') }}</p>
</div>
@endsection

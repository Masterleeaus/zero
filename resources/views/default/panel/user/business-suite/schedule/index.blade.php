@extends('default.panel.layout.app')

@section('title', __('Master Schedule'))

@section('content')
<div class="flex flex-col gap-y-6 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-heading">{{ __('Master Schedule') }}</h1>
        <a href="{{ route('dashboard.user.social-media.calendar') }}" class="lqd-btn lqd-btn-primary">
            {{ __('Add Schedule Item') }}
        </a>
    </div>
    <p class="text-gray-500 text-sm">{{ __('Quote follow-ups, booking times, job times, invoice reminders, and program checkpoints appear here.') }}</p>
</div>
@endsection

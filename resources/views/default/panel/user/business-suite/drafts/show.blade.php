@extends('default.panel.layout.app')

@section('title', __('Work Draft'))

@section('content')
<div class="flex flex-col gap-y-6 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-heading">{{ __('Work Draft') }}</h1>
        <a href="{{ route('dashboard.user.social-media.post.index') }}" class="lqd-btn lqd-btn-secondary">
            {{ __('Back to Work Drafts') }}
        </a>
    </div>
    <p class="text-gray-500 text-sm">{{ __('Draft details will appear here.') }}</p>
</div>
@endsection

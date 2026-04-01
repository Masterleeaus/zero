@extends('default.panel.layout.app')

@section('title', __('Performance Detail'))

@section('content')
<div class="flex flex-col gap-y-6 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-heading">{{ __('Performance Detail') }}</h1>
        <a href="#" class="lqd-btn lqd-btn-secondary" onclick="history.back()">{{ __('Back') }}</a>
    </div>
    <p class="text-gray-500 text-sm">{{ __('Detailed performance metrics will appear here.') }}</p>
</div>
@endsection

@extends('default.panel.layout.app')

@section('title', __('Performance'))

@section('content')
<div class="flex flex-col gap-y-6 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-heading">{{ __('Performance') }}</h1>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-500">{{ __('Total Drafts') }}</p>
            <p class="text-2xl font-bold text-heading mt-1">—</p>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-500">{{ __('Finalized Items') }}</p>
            <p class="text-2xl font-bold text-heading mt-1">—</p>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-500">{{ __('Work Items Created') }}</p>
            <p class="text-2xl font-bold text-heading mt-1">—</p>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-500">{{ __('Completion Rate') }}</p>
            <p class="text-2xl font-bold text-heading mt-1">—</p>
        </div>
    </div>
</div>
@endsection

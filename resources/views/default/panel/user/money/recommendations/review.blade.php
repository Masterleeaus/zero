@extends('panel.layout.app')
@section('title', __('Review Recommendation'))

@section('content')
    <div class="py-6 space-y-4 max-w-2xl">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.money.recommendations.index') }}" class="text-gray-400 hover:text-gray-600">←</a>
            <h1 class="text-xl font-semibold">{{ __('Review Recommendation') }}</h1>
        </div>

        <div class="bg-white rounded shadow p-5 space-y-3">
            <div class="flex justify-between items-start">
                <h2 class="font-semibold text-lg">{{ $recommendation->title }}</h2>
                <span class="px-2 py-0.5 rounded text-xs font-medium
                    {{ $recommendation->severity === 'critical' ? 'bg-red-100 text-red-700' : ($recommendation->severity === 'high' ? 'bg-orange-100 text-orange-700' : 'bg-yellow-100 text-yellow-700') }}">
                    {{ ucfirst($recommendation->severity) }}
                </span>
            </div>
            <p class="text-sm text-gray-700">{{ $recommendation->summary }}</p>
            <p class="text-sm text-gray-500"><strong>{{ __('Reason') }}:</strong> {{ $recommendation->reason }}</p>
            <p class="text-xs text-gray-400">{{ __('Source') }}: {{ $recommendation->source_service }} &middot; {{ __('Confidence') }}: {{ $recommendation->confidence }}%</p>
        </div>

        <div class="bg-white rounded shadow p-5 space-y-4">
            <h3 class="font-medium">{{ __('Action') }}</h3>

            <form method="POST" action="{{ route('dashboard.money.recommendations.approve', $recommendation) }}" class="space-y-3">
                @csrf
                <textarea name="review_notes" rows="3" placeholder="{{ __('Optional notes...') }}" class="w-full border rounded px-3 py-2 text-sm"></textarea>
                <div class="flex gap-3">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded text-sm">{{ __('Approve') }}</button>
                </div>
            </form>

            <form method="POST" action="{{ route('dashboard.money.recommendations.reject', $recommendation) }}" class="space-y-3">
                @csrf
                <textarea name="review_notes" rows="3" placeholder="{{ __('Reason for rejection...') }}" class="w-full border rounded px-3 py-2 text-sm"></textarea>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded text-sm">{{ __('Reject') }}</button>
            </form>

            <form method="POST" action="{{ route('dashboard.money.recommendations.dismiss', $recommendation) }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-gray-300 text-gray-700 rounded text-sm">{{ __('Dismiss') }}</button>
            </form>
        </div>
    </div>
@endsection

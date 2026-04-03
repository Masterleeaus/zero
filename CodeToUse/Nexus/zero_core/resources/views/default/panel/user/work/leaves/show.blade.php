@extends('panel.layout.app')
@section('title', __('Leave'))

@section('content')
    <div class="py-6 space-y-4">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold">{{ __('Leave details') }}</h2>
            <x-button href="{{ route('dashboard.work.leaves.edit', $leave) }}">{{ __('Edit') }}</x-button>
        </div>

        <x-card class="space-y-2">
            <div class="flex justify-between">
                <span class="text-slate-500">{{ __('User') }}</span>
                <span class="font-semibold">{{ $leave->user?->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">{{ __('Type') }}</span>
                <span class="font-semibold">{{ ucfirst($leave->type) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">{{ __('Status') }}</span>
                <span class="font-semibold">{{ $leave->status }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">{{ __('Start') }}</span>
                <span class="font-semibold">{{ $leave->start_date?->format('Y-m-d') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">{{ __('End') }}</span>
                <span class="font-semibold">{{ $leave->end_date?->format('Y-m-d') }}</span>
            </div>
            @if($leave->reason)
                <div class="flex justify-between">
                    <span class="text-slate-500">{{ __('Reason') }}</span>
                    <span class="font-semibold">{{ $leave->reason }}</span>
                </div>
            @endif
        </x-card>

        <x-card>
            <div class="font-semibold mb-2">{{ __('History') }}</div>
            <div class="space-y-2">
                @forelse($leave->histories as $history)
                    <div class="flex justify-between text-sm">
                        <span>{{ ucfirst($history->action) }}</span>
                        <span class="text-slate-500">{{ $history->created_at?->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-slate-500">{{ __('No history yet') }}</p>
                @endforelse
            </div>
        </x-card>
    </div>
@endsection

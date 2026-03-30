@extends('panel.layout.app')
@section('title', __('Shift'))

@section('content')
    <div class="py-6 space-y-4 max-w-3xl">
        <x-card class="space-y-3">
            <div class="flex justify-between items-center">
                <div>
                    <div class="text-sm text-slate-500">{{ __('User') }}</div>
                    <div class="font-semibold">{{ $shift->user?->name }}</div>
                </div>
                <x-badge>{{ __($shift->status) }}</x-badge>
            </div>
            <div>
                <div class="text-sm text-slate-500">{{ __('Service Job') }}</div>
                <div>{{ $shift->serviceJob?->title ?? '-' }}</div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-slate-500">{{ __('Start') }}</div>
                    <div>{{ $shift->start_at }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('End') }}</div>
                    <div>{{ $shift->end_at }}</div>
                </div>
            </div>
        </x-card>
    </div>
@endsection

@extends('default.layout.app')
@section('content')
    <div class="max-w-4xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Timesheet') }}</p>
                <h1 class="text-2xl font-semibold">{{ $timesheet['number'] ?? __('Timesheet') }}</h1>
                <p class="text-slate-500">{{ $timesheet['period'] ?? '' }}</p>
            </div>
            <div class="flex gap-2">
                <x-button variant="ghost">
                    <x-tabler-check class="size-4 text-emerald-600" />
                    {{ __('Approve') }}
                </x-button>
                <x-button variant="ghost">
                    <x-tabler-x class="size-4 text-rose-600" />
                    {{ __('Reject') }}
                </x-button>
            </div>
        </div>

        <x-card>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-slate-500">{{ __('Hours') }}</p>
                    <p class="font-semibold">{{ $timesheet['hours'] ?? '0' }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">{{ __('Total') }}</p>
                    <p class="font-semibold">${{ number_format($timesheet['total'] ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">{{ __('Status') }}</p>
                    <x-badge variant="info">{{ ucfirst($timesheet['status'] ?? 'pending') }}</x-badge>
                </div>
            </div>
        </x-card>
    </div>
@endsection


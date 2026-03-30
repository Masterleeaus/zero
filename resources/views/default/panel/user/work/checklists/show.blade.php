@extends('panel.layout.app')
@section('title', __('Checklist Item'))

@section('titlebar_actions')
    <x-button href="{{ route('dashboard.work.checklists.edit', $checklist) }}">
        <x-tabler-pencil class="size-4" />
        {{ __('Edit') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <x-card>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-slate-500">{{ __('Title') }}</div>
                    <div class="text-lg font-semibold">{{ $checklist->title }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Status') }}</div>
                    @if($checklist->is_completed)
                        <x-badge variant="success">{{ __('Done') }}</x-badge>
                    @else
                        <x-badge variant="warning">{{ __('Pending') }}</x-badge>
                    @endif
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Service Job') }}</div>
                    <div>{{ $checklist->job?->title }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Site') }}</div>
                    <div>{{ $checklist->job?->site?->name }}</div>
                </div>
            </div>

            @if($checklist->notes)
                <div class="mt-4">
                    <div class="text-sm text-slate-500">{{ __('Notes') }}</div>
                    <p class="whitespace-pre-line">{{ $checklist->notes }}</p>
                </div>
            @endif
        </x-card>
    </div>
@endsection

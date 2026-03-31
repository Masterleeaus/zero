@extends('panel.layout.app')
@section('title', __('Job Type') . ': ' . $type->name)
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.work.job-types.edit', $type) }}" variant="secondary">
        <x-tabler-pencil class="size-4" />
        {{ __('Edit') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <x-card>
            <dl class="text-sm">
                <dt class="text-slate-500">{{ __('Name') }}</dt>
                <dd class="font-medium">{{ $type->name }}</dd>
            </dl>
        </x-card>
    </div>
@endsection

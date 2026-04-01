@extends('panel.layout.app')
@section('title', __('Job Stage') . ': ' . $stage->name)
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.work.job-stages.edit', $stage) }}" variant="secondary">
        <x-tabler-pencil class="size-4" />
        {{ __('Edit') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <x-card>
            <dl class="grid md:grid-cols-3 gap-4 text-sm">
                <div>
                    <dt class="text-slate-500">{{ __('Name') }}</dt>
                    <dd class="font-medium">{{ $stage->name }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Type') }}</dt>
                    <dd><x-badge variant="info">{{ ucfirst($stage->stage_type) }}</x-badge></dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Sequence') }}</dt>
                    <dd>{{ $stage->sequence }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Color') }}</dt>
                    <dd class="flex items-center gap-2">
                        <span class="inline-block size-4 rounded border" style="background:{{ $stage->color }}"></span>
                        {{ $stage->color }}
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Default') }}</dt>
                    <dd>{{ $stage->is_default ? __('Yes') : __('No') }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Closed') }}</dt>
                    <dd>{{ $stage->is_closed ? __('Yes') : __('No') }}</dd>
                </div>
            </dl>
            @if($stage->description)
                <div class="mt-4 text-sm">
                    <p class="text-slate-500">{{ __('Description') }}</p>
                    <p>{{ $stage->description }}</p>
                </div>
            @endif
        </x-card>
    </div>
@endsection

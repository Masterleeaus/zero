@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Branch') }}</p>
                <h1 class="text-2xl font-semibold">{{ $branch->name }}</h1>
            </div>
            <x-button href="{{ route('dashboard.team.service-area-branches.edit', $branch) }}" variant="ghost">
                {{ __('Edit') }}
            </x-button>
        </div>

        <x-card>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                @if($branch->district)
                    <div>
                        <dt class="text-slate-500">{{ __('District') }}</dt>
                        <dd class="font-semibold">{{ $branch->district->name }}</dd>
                    </div>
                @endif
                @if($branch->district?->region)
                    <div>
                        <dt class="text-slate-500">{{ __('Region') }}</dt>
                        <dd class="font-semibold">{{ $branch->district->region->name }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-slate-500">{{ __('Zones') }}</dt>
                    <dd class="font-semibold">{{ $branch->service_areas_count }}</dd>
                </div>
                @if($branch->description)
                    <div class="col-span-2">
                        <dt class="text-slate-500">{{ __('Description') }}</dt>
                        <dd>{{ $branch->description }}</dd>
                    </div>
                @endif
            </dl>
        </x-card>
    </div>
@endsection

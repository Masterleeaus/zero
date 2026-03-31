@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Zone') }}</p>
                <h1 class="text-2xl font-semibold">{{ $zone->name }}</h1>
                @if($zone->code)
                    <p class="text-slate-500">{{ __('Code') }}: {{ $zone->code }}</p>
                @endif
            </div>
            <div class="flex gap-2">
                <x-button href="{{ route('dashboard.team.zones.edit', $zone) }}" variant="ghost">
                    {{ __('Edit') }}
                </x-button>
            </div>
        </div>

        <x-card>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-slate-500">{{ __('Type') }}</dt>
                    <dd class="font-semibold">{{ $zone->type ? ucfirst($zone->type) : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Sites') }}</dt>
                    <dd class="font-semibold">{{ $zone->sites_count }}</dd>
                </div>
                @if($zone->branch)
                    <div>
                        <dt class="text-slate-500">{{ __('Branch') }}</dt>
                        <dd class="font-semibold">{{ $zone->branch->name }}</dd>
                    </div>
                @endif
                @if($zone->branch?->district)
                    <div>
                        <dt class="text-slate-500">{{ __('District') }}</dt>
                        <dd class="font-semibold">{{ $zone->branch->district->name }}</dd>
                    </div>
                @endif
                @if($zone->branch?->district?->region)
                    <div>
                        <dt class="text-slate-500">{{ __('Region') }}</dt>
                        <dd class="font-semibold">{{ $zone->branch->district->region->name }}</dd>
                    </div>
                @endif
                @if($zone->description)
                    <div class="col-span-2">
                        <dt class="text-slate-500">{{ __('Description') }}</dt>
                        <dd>{{ $zone->description }}</dd>
                    </div>
                @endif
                @if($zone->zip_codes)
                    <div class="col-span-2">
                        <dt class="text-slate-500">{{ __('ZIP / Postcode Coverage') }}</dt>
                        <dd>{{ $zone->zip_codes }}</dd>
                    </div>
                @endif
            </dl>
        </x-card>
    </div>
@endsection

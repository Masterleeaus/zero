@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('District') }}</p>
                <h1 class="text-2xl font-semibold">{{ $district->name }}</h1>
            </div>
            <x-button href="{{ route('dashboard.team.service-area-districts.edit', $district) }}" variant="ghost">
                {{ __('Edit') }}
            </x-button>
        </div>

        <x-card>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                @if($district->region)
                    <div>
                        <dt class="text-slate-500">{{ __('Region') }}</dt>
                        <dd class="font-semibold">{{ $district->region->name }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-slate-500">{{ __('Branches') }}</dt>
                    <dd class="font-semibold">{{ $district->branches_count }}</dd>
                </div>
                @if($district->description)
                    <div class="col-span-2">
                        <dt class="text-slate-500">{{ __('Description') }}</dt>
                        <dd>{{ $district->description }}</dd>
                    </div>
                @endif
            </dl>
        </x-card>
    </div>
@endsection

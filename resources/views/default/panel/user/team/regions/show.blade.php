@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Region') }}</p>
                <h1 class="text-2xl font-semibold">{{ $region->name }}</h1>
                @if($region->description)
                    <p class="text-slate-500 text-sm">{{ $region->description }}</p>
                @endif
            </div>
            <div class="flex gap-2">
                <x-button href="{{ route('dashboard.team.regions.edit', $region) }}" variant="secondary">
                    {{ __('Edit') }}
                </x-button>
                <x-button href="{{ route('dashboard.team.regions.index') }}" variant="ghost">
                    {{ __('Back') }}
                </x-button>
            </div>
        </div>

        <x-card>
            <dl class="text-sm space-y-3">
                <div>
                    <dt class="text-slate-500">{{ __('Manager') }}</dt>
                    <dd class="font-semibold">{{ $region->manager?->name ?? '—' }}</dd>
                </div>
            </dl>
        </x-card>

        @if($region->districts->isNotEmpty())
            <x-card>
                <h2 class="font-semibold mb-3">{{ __('Districts') }}</h2>
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Branches') }}</th>
                        </tr>
                    </x-slot:head>
                    <x-slot:body>
                        @foreach($region->districts as $district)
                            <tr>
                                <td>
                                    <a href="{{ route('dashboard.team.districts.show', $district) }}" class="hover:underline">
                                        {{ $district->name }}
                                    </a>
                                </td>
                                <td>{{ $district->branches->count() }}</td>
                            </tr>
                        @endforeach
                    </x-slot:body>
                </x-table>
            </x-card>
        @endif
    </div>
@endsection

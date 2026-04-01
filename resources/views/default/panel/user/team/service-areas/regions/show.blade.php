@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Region') }}</p>
                <h1 class="text-2xl font-semibold">{{ $region->name }}</h1>
            </div>
            <x-button href="{{ route('dashboard.team.service-area-regions.edit', $region) }}" variant="ghost">
                {{ __('Edit') }}
            </x-button>
        </div>

        <x-card>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-slate-500">{{ __('Districts') }}</dt>
                    <dd class="font-semibold">{{ $region->districts_count }}</dd>
                </div>
                @if($region->description)
                    <div class="col-span-2">
                        <dt class="text-slate-500">{{ __('Description') }}</dt>
                        <dd>{{ $region->description }}</dd>
                    </div>
                @endif
            </dl>
        </x-card>

        @if($region->districts->isNotEmpty())
            <x-table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('District') }}</th>
                        <th>{{ __('Description') }}</th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @foreach($region->districts as $district)
                        <tr>
                            <td class="font-semibold">{{ $district->name }}</td>
                            <td>{{ $district->description ?? '—' }}</td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-table>
        @endif
    </div>
@endsection

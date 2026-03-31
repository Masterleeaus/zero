@extends('default.layout.app')
@section('content')
    <div class="max-w-5xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Zones') }}</p>
                <h1 class="text-2xl font-semibold">{{ __('Operational Regions') }}</h1>
            </div>
            <x-button href="{{ route('dashboard.team.zones.create') }}">
                <x-tabler-plus class="size-4" />
                {{ __('Add Zone') }}
            </x-button>
        </div>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Code') }}</th>
                    <th>{{ __('Sites') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @foreach($zones as $zone)
                    <tr>
                        <td class="font-semibold">{{ $zone['name'] }}</td>
                        <td>{{ $zone['code'] }}</td>
                        <td>{{ $zone['sites'] }}</td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-table>
    </div>
@endsection


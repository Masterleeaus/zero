@extends('default.layout.app')
@section('content')
    <div class="max-w-5xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Territory Hierarchy') }}</p>
                <h1 class="text-2xl font-semibold">{{ __('Regions') }}</h1>
            </div>
            <x-button href="{{ route('dashboard.team.regions.create') }}">
                <x-tabler-plus class="size-4" />
                {{ __('Add Region') }}
            </x-button>
        </div>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Manager') }}</th>
                    <th>{{ __('Districts') }}</th>
                    <th></th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($regions as $region)
                    <tr>
                        <td class="font-semibold">
                            <a href="{{ route('dashboard.team.regions.show', $region) }}" class="hover:underline">
                                {{ $region->name }}
                            </a>
                        </td>
                        <td>{{ $region->manager?->name ?? '—' }}</td>
                        <td>{{ $region->districts_count }}</td>
                        <td class="text-right">
                            <x-button variant="ghost" href="{{ route('dashboard.team.regions.edit', $region) }}">
                                {{ __('Edit') }}
                            </x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-slate-400 py-6">{{ __('No regions yet.') }}</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $regions->links() }}
    </div>
@endsection

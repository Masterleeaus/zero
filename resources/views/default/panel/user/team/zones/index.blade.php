@extends('default.layout.app')
@section('content')
    <div class="max-w-5xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Territories') }}</p>
                <h1 class="text-2xl font-semibold">{{ __('Territories') }}</h1>
            </div>
            <x-button href="{{ route('dashboard.team.zones.create') }}">
                <x-tabler-plus class="size-4" />
                {{ __('Add Territory') }}
            </x-button>
        </div>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Code') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Branch') }}</th>
                    <th>{{ __('Sites') }}</th>
                    <th></th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($zones as $zone)
                    <tr>
                        <td class="font-semibold">
                            <a href="{{ route('dashboard.team.zones.show', $zone) }}" class="hover:underline">{{ $zone->name }}</a>
                        </td>
                        <td>{{ $zone->code ?? '—' }}</td>
                        <td>{{ $zone->type ? ucfirst($zone->type) : '—' }}</td>
                        <td>{{ $zone->branch?->name ?? '—' }}</td>
                        <td>{{ $zone->sites_count }}</td>
                        <td>
                            <a href="{{ route('dashboard.team.zones.edit', $zone) }}" class="text-sm text-slate-500 hover:underline">{{ __('Edit') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-slate-400 py-6">{{ __('No zones yet.') }}</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $territories->links() }}
    </div>
@endsection

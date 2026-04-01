@extends('default.layout.app')
@section('content')
    <div class="max-w-5xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Service Areas') }}</p>
                <h1 class="text-2xl font-semibold">{{ __('Branches') }}</h1>
            </div>
            <x-button href="{{ route('dashboard.team.service-area-branches.create') }}">
                <x-tabler-plus class="size-4" />
                {{ __('Add Branch') }}
            </x-button>
        </div>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('District') }}</th>
                    <th>{{ __('Region') }}</th>
                    <th>{{ __('Zones') }}</th>
                    <th></th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($branches as $branch)
                    <tr>
                        <td class="font-semibold">
                            <a href="{{ route('dashboard.team.service-area-branches.show', $branch) }}" class="hover:underline">{{ $branch->name }}</a>
                        </td>
                        <td>{{ $branch->district?->name ?? '—' }}</td>
                        <td>{{ $branch->district?->region?->name ?? '—' }}</td>
                        <td>{{ $branch->service_areas_count }}</td>
                        <td>
                            <a href="{{ route('dashboard.team.service-area-branches.edit', $branch) }}" class="text-sm text-slate-500 hover:underline">{{ __('Edit') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-slate-400 py-6">{{ __('No branches yet.') }}</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>
    </div>
@endsection

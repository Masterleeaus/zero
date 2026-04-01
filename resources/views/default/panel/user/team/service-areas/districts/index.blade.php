@extends('default.layout.app')
@section('content')
    <div class="max-w-5xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Service Areas') }}</p>
                <h1 class="text-2xl font-semibold">{{ __('Districts') }}</h1>
            </div>
            <x-button href="{{ route('dashboard.team.service-area-districts.create') }}">
                <x-tabler-plus class="size-4" />
                {{ __('Add District') }}
            </x-button>
        </div>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Region') }}</th>
                    <th>{{ __('Branches') }}</th>
                    <th></th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($districts as $district)
                    <tr>
                        <td class="font-semibold">
                            <a href="{{ route('dashboard.team.service-area-districts.show', $district) }}" class="hover:underline">{{ $district->name }}</a>
                        </td>
                        <td>{{ $district->region?->name ?? '—' }}</td>
                        <td>{{ $district->branches_count }}</td>
                        <td>
                            <a href="{{ route('dashboard.team.service-area-districts.edit', $district) }}" class="text-sm text-slate-500 hover:underline">{{ __('Edit') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-slate-400 py-6">{{ __('No districts yet.') }}</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>
    </div>
@endsection

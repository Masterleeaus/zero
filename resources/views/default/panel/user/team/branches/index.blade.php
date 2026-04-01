@extends('default.layout.app')
@section('content')
    <div class="max-w-5xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Territory Hierarchy') }}</p>
                <h1 class="text-2xl font-semibold">{{ __('Branches') }}</h1>
            </div>
            <x-button href="{{ route('dashboard.team.branches.create') }}">
                <x-tabler-plus class="size-4" />
                {{ __('Add Branch') }}
            </x-button>
        </div>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('District') }}</th>
                    <th>{{ __('Manager') }}</th>
                    <th>{{ __('Territories') }}</th>
                    <th></th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($branches as $branch)
                    <tr>
                        <td class="font-semibold">
                            <a href="{{ route('dashboard.team.branches.show', $branch) }}" class="hover:underline">
                                {{ $branch->name }}
                            </a>
                        </td>
                        <td>{{ $branch->district?->name ?? '—' }}</td>
                        <td>{{ $branch->manager?->name ?? '—' }}</td>
                        <td>{{ $branch->territories_count }}</td>
                        <td class="text-right">
                            <x-button variant="ghost" href="{{ route('dashboard.team.branches.edit', $branch) }}">
                                {{ __('Edit') }}
                            </x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-slate-400 py-6">{{ __('No branches yet.') }}</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $branches->links() }}
    </div>
@endsection

@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('District') }}</p>
                <h1 class="text-2xl font-semibold">{{ $district->name }}</h1>
                @if($district->description)
                    <p class="text-slate-500 text-sm">{{ $district->description }}</p>
                @endif
            </div>
            <div class="flex gap-2">
                <x-button href="{{ route('dashboard.team.districts.edit', $district) }}" variant="secondary">
                    {{ __('Edit') }}
                </x-button>
                <x-button href="{{ route('dashboard.team.districts.index') }}" variant="ghost">
                    {{ __('Back') }}
                </x-button>
            </div>
        </div>

        <x-card>
            <dl class="grid md:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-slate-500">{{ __('Region') }}</dt>
                    <dd class="font-semibold">
                        @if($district->region)
                            <a href="{{ route('dashboard.team.regions.show', $district->region) }}" class="hover:underline">
                                {{ $district->region->name }}
                            </a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Manager') }}</dt>
                    <dd class="font-semibold">{{ $district->manager?->name ?? '—' }}</dd>
                </div>
            </dl>
        </x-card>

        @if($district->branches->isNotEmpty())
            <x-card>
                <h2 class="font-semibold mb-3">{{ __('Branches') }}</h2>
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Territories') }}</th>
                        </tr>
                    </x-slot:head>
                    <x-slot:body>
                        @foreach($district->branches as $branch)
                            <tr>
                                <td>
                                    <a href="{{ route('dashboard.team.branches.show', $branch) }}" class="hover:underline">
                                        {{ $branch->name }}
                                    </a>
                                </td>
                                <td>{{ $branch->territories->count() }}</td>
                            </tr>
                        @endforeach
                    </x-slot:body>
                </x-table>
            </x-card>
        @endif
    </div>
@endsection

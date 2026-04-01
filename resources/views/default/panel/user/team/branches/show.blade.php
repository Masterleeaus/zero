@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Branch') }}</p>
                <h1 class="text-2xl font-semibold">{{ $branch->name }}</h1>
                @if($branch->description)
                    <p class="text-slate-500 text-sm">{{ $branch->description }}</p>
                @endif
            </div>
            <div class="flex gap-2">
                <x-button href="{{ route('dashboard.team.branches.edit', $branch) }}" variant="secondary">
                    {{ __('Edit') }}
                </x-button>
                <x-button href="{{ route('dashboard.team.branches.index') }}" variant="ghost">
                    {{ __('Back') }}
                </x-button>
            </div>
        </div>

        <x-card>
            <dl class="grid md:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-slate-500">{{ __('District') }}</dt>
                    <dd class="font-semibold">
                        @if($branch->district)
                            <a href="{{ route('dashboard.team.districts.show', $branch->district) }}" class="hover:underline">
                                {{ $branch->district->name }}
                            </a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Region') }}</dt>
                    <dd class="font-semibold">{{ $branch->district?->region?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Manager') }}</dt>
                    <dd class="font-semibold">{{ $branch->manager?->name ?? '—' }}</dd>
                </div>
            </dl>
        </x-card>

        @if($branch->territories->isNotEmpty())
            <x-card>
                <h2 class="font-semibold mb-3">{{ __('Territories') }}</h2>
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Type') }}</th>
                        </tr>
                    </x-slot:head>
                    <x-slot:body>
                        @foreach($branch->territories as $territory)
                            <tr>
                                <td>
                                    <a href="{{ route('dashboard.team.zones.show', $territory) }}" class="hover:underline">
                                        {{ $territory->name }}
                                    </a>
                                </td>
                                <td>{{ $territory->type ? ucfirst($territory->type) : '—' }}</td>
                            </tr>
                        @endforeach
                    </x-slot:body>
                </x-table>
            </x-card>
        @endif
    </div>
@endsection

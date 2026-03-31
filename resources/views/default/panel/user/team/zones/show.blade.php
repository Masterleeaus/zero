@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Territory') }}</p>
                <h1 class="text-2xl font-semibold">{{ $territory->name }}</h1>
                @if($territory->type)
                    <p class="text-slate-500 text-sm">{{ ucfirst($territory->type) }}</p>
                @endif
            </div>
            <div class="flex gap-2">
                <x-button href="{{ route('dashboard.team.zones.edit', $territory) }}" variant="secondary">
                    {{ __('Edit') }}
                </x-button>
                <x-button href="{{ route('dashboard.team.zones.index') }}" variant="ghost">
                    {{ __('Back') }}
                </x-button>
            </div>
        </div>

        <x-card>
            <dl class="grid md:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-slate-500">{{ __('Branch') }}</dt>
                    <dd class="font-semibold">{{ $territory->branch?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('District') }}</dt>
                    <dd class="font-semibold">{{ $territory->district?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Region') }}</dt>
                    <dd class="font-semibold">{{ $territory->region?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Description') }}</dt>
                    <dd>{{ $territory->description ?: '—' }}</dd>
                </div>
                @if($territory->zip_codes)
                    <div class="md:col-span-2">
                        <dt class="text-slate-500">{{ __('ZIP Codes') }}</dt>
                        <dd class="font-mono text-xs mt-1">{{ $territory->zip_codes }}</dd>
                    </div>
                @endif
            </dl>
        </x-card>

        @if($territory->sites->isNotEmpty())
            <x-card>
                <h2 class="font-semibold mb-3">{{ __('Sites') }}</h2>
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Status') }}</th>
                        </tr>
                    </x-slot:head>
                    <x-slot:body>
                        @foreach($territory->sites as $site)
                            <tr>
                                <td>{{ $site->name }}</td>
                                <td>{{ ucfirst($site->status) }}</td>
                            </tr>
                        @endforeach
                    </x-slot:body>
                </x-table>
            </x-card>
        @endif
    </div>
@endsection

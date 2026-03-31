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
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Branch') }}</th>
                    <th>{{ __('ZIP Codes') }}</th>
                    <th></th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($territories as $territory)
                    <tr>
                        <td class="font-semibold">
                            <a href="{{ route('dashboard.team.zones.show', $territory) }}" class="hover:underline">
                                {{ $territory->name }}
                            </a>
                        </td>
                        <td>{{ $territory->type ? ucfirst($territory->type) : '—' }}</td>
                        <td>{{ $territory->branch?->name ?? '—' }}</td>
                        <td class="text-slate-500 text-sm truncate max-w-xs">{{ $territory->zip_codes ? \Illuminate\Support\Str::limit($territory->zip_codes, 60) : '—' }}</td>
                        <td class="text-right">
                            <x-button variant="ghost" href="{{ route('dashboard.team.zones.edit', $territory) }}">
                                {{ __('Edit') }}
                            </x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-slate-400 py-6">{{ __('No territories yet.') }}</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $territories->links() }}
    </div>
@endsection

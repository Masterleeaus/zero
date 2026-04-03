@extends('panel.layout.app')
@section('title', __('Warehouses'))

@section('titlebar_actions')
    <x-button href="{{ route('dashboard.inventory.warehouses.create') }}">
        <x-tabler-building-warehouse class="size-4" />
        {{ __('New Warehouse') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <form method="get" class="flex gap-3">
            <x-input name="q" value="{{ request('q') }}" placeholder="{{ __('Search warehouses') }}" />
            <x-button type="submit" variant="secondary">
                <x-tabler-search class="size-4" />
                {{ __('Search') }}
            </x-button>
        </form>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Code') }}</th>
                    <th>{{ __('Address') }}</th>
                    <th>{{ __('Default') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Action') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($warehouses as $warehouse)
                    <tr>
                        <td>{{ $warehouse->name }}</td>
                        <td>{{ $warehouse->code }}</td>
                        <td>{{ $warehouse->address }}</td>
                        <td>
                            @if($warehouse->is_default)
                                <x-badge variant="success">{{ __('Default') }}</x-badge>
                            @endif
                        </td>
                        <td><x-badge variant="{{ $warehouse->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($warehouse->status) }}</x-badge></td>
                        <td class="text-end whitespace-nowrap">
                            <x-button variant="ghost-shadow" size="none" class="size-9"
                                      href="{{ route('dashboard.inventory.warehouses.show', $warehouse) }}">
                                <x-tabler-eye class="size-4" />
                            </x-button>
                            <x-button variant="ghost-shadow" size="none" class="size-9"
                                      href="{{ route('dashboard.inventory.warehouses.edit', $warehouse) }}">
                                <x-tabler-edit class="size-4" />
                            </x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-slate-500 py-6">{{ __('No warehouses yet') }}</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $warehouses->links() }}
    </div>
@endsection

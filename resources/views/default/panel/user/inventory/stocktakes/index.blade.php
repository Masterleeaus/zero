@extends('panel.layout.app')
@section('title', __('Stocktakes'))

@section('titlebar_actions')
    <x-button href="{{ route('dashboard.inventory.stocktakes.create') }}">
        <x-tabler-clipboard-check class="size-4" />
        {{ __('New Stocktake') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Ref') }}</th>
                    <th>{{ __('Warehouse') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Notes') }}</th>
                    <th>{{ __('Created') }}</th>
                    <th class="text-end">{{ __('Action') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($stocktakes as $stocktake)
                    <tr>
                        <td>{{ $stocktake->ref ?? "ST-{$stocktake->id}" }}</td>
                        <td>{{ $stocktake->warehouse?->name }}</td>
                        <td><x-badge variant="info">{{ ucfirst($stocktake->status) }}</x-badge></td>
                        <td>{{ Str::limit($stocktake->notes, 40) }}</td>
                        <td>{{ $stocktake->created_at->toFormattedDateString() }}</td>
                        <td class="text-end whitespace-nowrap">
                            <x-button variant="ghost-shadow" size="none" class="size-9"
                                      href="{{ route('dashboard.inventory.stocktakes.show', $stocktake) }}">
                                <x-tabler-eye class="size-4" />
                            </x-button>
                            <x-button variant="ghost-shadow" size="none" class="size-9"
                                      href="{{ route('dashboard.inventory.stocktakes.edit', $stocktake) }}">
                                <x-tabler-edit class="size-4" />
                            </x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-slate-500 py-6">{{ __('No stocktakes yet') }}</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $stocktakes->links() }}
    </div>
@endsection

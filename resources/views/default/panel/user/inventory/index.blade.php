@extends('panel.layout.app')
@section('title', __('Inventory'))

@section('titlebar_actions')
    <x-button href="{{ route('dashboard.inventory.items.create') }}">
        <x-tabler-package class="size-4" />
        {{ __('New Item') }}
    </x-button>
    <x-button href="{{ route('dashboard.inventory.purchase-orders.create') }}" variant="ghost">
        <x-tabler-file-invoice class="size-4" />
        {{ __('New PO') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                <p class="text-sm text-slate-500">{{ __('Suppliers') }}</p>
                <p class="text-2xl font-bold">{{ $stats['supplier_count'] }}</p>
                <a href="{{ route('dashboard.inventory.suppliers.index') }}" class="text-xs text-blue-500 hover:underline">{{ __('View all') }}</a>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                <p class="text-sm text-slate-500">{{ __('Items') }}</p>
                <p class="text-2xl font-bold">{{ $stats['item_count'] }}</p>
                <a href="{{ route('dashboard.inventory.items.index') }}" class="text-xs text-blue-500 hover:underline">{{ __('View all') }}</a>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                <p class="text-sm text-slate-500">{{ __('Warehouses') }}</p>
                <p class="text-2xl font-bold">{{ $stats['warehouse_count'] }}</p>
                <a href="{{ route('dashboard.inventory.warehouses.index') }}" class="text-xs text-blue-500 hover:underline">{{ __('View all') }}</a>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                <p class="text-sm text-slate-500">{{ __('Low Stock') }}</p>
                <p class="text-2xl font-bold text-red-600">{{ $stats['low_stock_count'] }}</p>
                <a href="{{ route('dashboard.inventory.items.index') }}" class="text-xs text-blue-500 hover:underline">{{ __('View items') }}</a>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">{{ __('Recent Stock Movements') }}</h3>
            <x-table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('Item') }}</th>
                        <th>{{ __('Warehouse') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Qty') }}</th>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Date') }}</th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @forelse($recentMovements as $movement)
                        <tr>
                            <td>{{ $movement->item?->name }}</td>
                            <td>{{ $movement->warehouse?->name }}</td>
                            <td><x-badge variant="info">{{ ucfirst($movement->type) }}</x-badge></td>
                            <td class="{{ $movement->qty_change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $movement->qty_change >= 0 ? '+' : '' }}{{ $movement->qty_change }}
                            </td>
                            <td>{{ $movement->reference }}</td>
                            <td>{{ $movement->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-slate-500 py-6">{{ __('No movements yet') }}</td>
                        </tr>
                    @endforelse
                </x-slot:body>
            </x-table>
        </div>
    </div>
@endsection

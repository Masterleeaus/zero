@extends('panel.layout.app')
@section('title', __('Inventory Items'))

@section('titlebar_actions')
    <x-button href="{{ route('dashboard.inventory.items.create') }}">
        <x-tabler-package class="size-4" />
        {{ __('New Item') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <form method="get" class="grid md:grid-cols-3 gap-3">
            <x-input name="q" value="{{ request('q') }}" placeholder="{{ __('Search items') }}" />
            <x-select name="status">
                <option value="">{{ __('All statuses') }}</option>
                <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
                <option value="inactive" @selected(request('status') === 'inactive')>{{ __('Inactive') }}</option>
            </x-select>
            <div class="flex gap-3">
                <x-button type="submit" variant="secondary">
                    <x-tabler-search class="size-4" />
                    {{ __('Filter') }}
                </x-button>
                <x-button href="{{ route('dashboard.inventory.items.index') }}" variant="ghost">{{ __('Reset') }}</x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('SKU') }}</th>
                    <th>{{ __('Category') }}</th>
                    <th>{{ __('On Hand') }}</th>
                    <th>{{ __('Unit Price') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Action') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->sku }}</td>
                        <td>{{ $item->category }}</td>
                        <td class="{{ $item->qty_on_hand <= $item->reorder_point ? 'text-red-600 font-semibold' : '' }}">
                            {{ $item->qty_on_hand }} {{ $item->unit }}
                        </td>
                        <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td><x-badge variant="{{ $item->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($item->status) }}</x-badge></td>
                        <td class="text-end whitespace-nowrap">
                            <x-button variant="ghost-shadow" size="none" class="size-9"
                                      href="{{ route('dashboard.inventory.items.show', $item) }}">
                                <x-tabler-eye class="size-4" />
                            </x-button>
                            <x-button variant="ghost-shadow" size="none" class="size-9"
                                      href="{{ route('dashboard.inventory.items.edit', $item) }}">
                                <x-tabler-edit class="size-4" />
                            </x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-slate-500 py-6">{{ __('No items yet') }}</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $items->links() }}
    </div>
@endsection

@extends('panel.layout.app')
@section('title', __('Purchase Orders'))

@section('titlebar_actions')
    <x-button href="{{ route('dashboard.inventory.purchase-orders.create') }}">
        <x-tabler-file-invoice class="size-4" />
        {{ __('New Purchase Order') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <form method="get" class="grid md:grid-cols-3 gap-3">
            <x-input name="q" value="{{ request('q') }}" placeholder="{{ __('Search PO number') }}" />
            <x-select name="status">
                <option value="">{{ __('All statuses') }}</option>
                @foreach(['draft', 'sent', 'partial', 'received', 'cancelled'] as $option)
                    <option value="{{ $option }}" @selected(request('status') === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </x-select>
            <div class="flex gap-3">
                <x-button type="submit" variant="secondary">
                    <x-tabler-search class="size-4" />
                    {{ __('Filter') }}
                </x-button>
                <x-button href="{{ route('dashboard.inventory.purchase-orders.index') }}" variant="ghost">{{ __('Reset') }}</x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('PO Number') }}</th>
                    <th>{{ __('Supplier') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Total') }}</th>
                    <th>{{ __('Order Date') }}</th>
                    <th class="text-end">{{ __('Action') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($orders as $order)
                    <tr>
                        <td>{{ $order->po_number }}</td>
                        <td>{{ $order->supplier?->name }}</td>
                        <td><x-badge variant="info">{{ ucfirst($order->status) }}</x-badge></td>
                        <td>{{ number_format((float) $order->total_amount, 2) }}</td>
                        <td>{{ optional($order->order_date)->toFormattedDateString() }}</td>
                        <td class="text-end whitespace-nowrap">
                            <x-button variant="ghost-shadow" size="none" class="size-9"
                                      href="{{ route('dashboard.inventory.purchase-orders.show', $order) }}">
                                <x-tabler-eye class="size-4" />
                            </x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-slate-500 py-6">{{ __('No purchase orders yet') }}</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $orders->links() }}
    </div>
@endsection

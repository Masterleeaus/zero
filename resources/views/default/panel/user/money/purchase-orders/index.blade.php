@extends('panel.layout.app')
@section('title', __('Purchase Orders'))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.money.purchase-orders.create') }}">
        <x-tabler-file-invoice class="size-4" />
        {{ __('New PO') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <form method="get" class="grid md:grid-cols-3 gap-3">
            <x-input name="q" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Search PO number') }}" />
            <x-select name="status">
                <option value="">{{ __('All statuses') }}</option>
                @foreach(['draft','sent','partial','received','cancelled'] as $option)
                    <option value="{{ $option }}" @selected(($filters['status'] ?? '') === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </x-select>
            <div class="flex gap-3">
                <x-button type="submit" variant="secondary">
                    <x-tabler-search class="size-4" />
                    {{ __('Filter') }}
                </x-button>
                <x-button href="{{ route('dashboard.money.purchase-orders.index') }}" variant="ghost">
                    {{ __('Reset') }}
                </x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('PO #') }}</th>
                    <th>{{ __('Supplier') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Total') }}</th>
                    <th></th>
                </tr>
            </x-slot:head>
            @forelse($purchaseOrders as $po)
                <tr>
                    <td>
                        <a href="{{ route('dashboard.money.purchase-orders.show', $po) }}" class="font-medium hover:underline">
                            {{ $po->po_number }}
                        </a>
                    </td>
                    <td>{{ $po->supplier?->name }}</td>
                    <td>{{ optional($po->order_date)->toFormattedDateString() }}</td>
                    <td><x-badge>{{ ucfirst($po->status) }}</x-badge></td>
                    <td>{{ number_format($po->total_amount, 2) }} {{ $po->currency_code }}</td>
                    <td>
                        <x-button href="{{ route('dashboard.money.purchase-orders.edit', $po) }}" size="sm" variant="ghost">
                            {{ __('Edit') }}
                        </x-button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-slate-400 py-8">{{ __('No purchase orders found.') }}</td>
                </tr>
            @endforelse
        </x-table>

        {{ $purchaseOrders->links() }}
    </div>
@endsection

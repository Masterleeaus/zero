@extends('panel.layout.app')
@section('title', __('PO: :po', ['po' => $purchaseOrder->po_number]))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.money.purchase-orders.edit', $purchaseOrder) }}" variant="secondary">
        {{ __('Edit') }}
    </x-button>
    <x-button href="{{ route('dashboard.money.supplier-bills.create') }}?purchase_order_id={{ $purchaseOrder->id }}" variant="ghost">
        {{ __('Create Bill') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-6">
        <x-card>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <div class="text-sm text-slate-500">{{ __('Supplier') }}</div>
                    <a href="{{ route('dashboard.money.suppliers.show', $purchaseOrder->supplier) }}" class="hover:underline">
                        {{ $purchaseOrder->supplier?->name }}
                    </a>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Status') }}</div>
                    <x-badge>{{ ucfirst($purchaseOrder->status) }}</x-badge>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Order Date') }}</div>
                    <div>{{ optional($purchaseOrder->order_date)->toFormattedDateString() }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Expected Date') }}</div>
                    <div>{{ optional($purchaseOrder->expected_date)->toFormattedDateString() ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Currency') }}</div>
                    <div>{{ $purchaseOrder->currency_code }}</div>
                </div>
                @if($purchaseOrder->service_job_id)
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Job ID') }}</div>
                        <div>{{ $purchaseOrder->service_job_id }}</div>
                    </div>
                @endif
                <div>
                    <div class="text-sm text-slate-500">{{ __('Subtotal') }}</div>
                    <div>{{ number_format($purchaseOrder->subtotal, 2) }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Tax') }}</div>
                    <div>{{ number_format($purchaseOrder->tax_amount, 2) }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Total') }}</div>
                    <div class="font-semibold">{{ number_format($purchaseOrder->total_amount, 2) }} {{ $purchaseOrder->currency_code }}</div>
                </div>
            </div>
        </x-card>

        @if($purchaseOrder->items->count())
            <x-card title="{{ __('Line Items') }}">
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Qty Ordered') }}</th>
                            <th>{{ __('Qty Received') }}</th>
                            <th>{{ __('Unit Price') }}</th>
                            <th>{{ __('Line Total') }}</th>
                        </tr>
                    </x-slot:head>
                    @foreach($purchaseOrder->items as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td>{{ $item->qty_ordered }}</td>
                            <td>{{ $item->qty_received }}</td>
                            <td>{{ number_format($item->unit_price, 4) }}</td>
                            <td>{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </x-table>
            </x-card>
        @endif
    </div>
@endsection

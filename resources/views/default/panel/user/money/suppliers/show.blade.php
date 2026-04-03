@extends('panel.layout.app')
@section('title', $supplier->name)
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.money.suppliers.edit', $supplier) }}" variant="secondary">
        {{ __('Edit') }}
    </x-button>
    <x-button href="{{ route('dashboard.money.supplier-bills.create') }}?supplier_id={{ $supplier->id }}" variant="ghost">
        {{ __('New Bill') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-6">
        <x-card>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <div class="text-sm text-slate-500">{{ __('Email') }}</div>
                    <div>{{ $supplier->email ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Phone') }}</div>
                    <div>{{ $supplier->phone ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Tax Number') }}</div>
                    <div>{{ $supplier->tax_number ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Payment Terms') }}</div>
                    <div>{{ $supplier->payment_terms ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Currency') }}</div>
                    <div>{{ $supplier->currency_code ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Status') }}</div>
                    <x-badge variant="{{ $supplier->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($supplier->status) }}</x-badge>
                </div>
            </div>
            @if($supplier->notes)
                <div class="mt-4 border-t pt-4 text-sm text-slate-600">{{ $supplier->notes }}</div>
            @endif
        </x-card>

        @if($supplier->purchaseOrders->count())
            <x-card title="{{ __('Purchase Orders') }}">
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th>{{ __('PO #') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Total') }}</th>
                        </tr>
                    </x-slot:head>
                    @foreach($supplier->purchaseOrders as $po)
                        <tr>
                            <td>
                                <a href="{{ route('dashboard.money.purchase-orders.show', $po) }}" class="hover:underline">
                                    {{ $po->po_number }}
                                </a>
                            </td>
                            <td>{{ optional($po->order_date)->toFormattedDateString() }}</td>
                            <td><x-badge>{{ ucfirst($po->status) }}</x-badge></td>
                            <td>{{ number_format($po->total_amount, 2) }} {{ $po->currency_code }}</td>
                        </tr>
                    @endforeach
                </x-table>
            </x-card>
        @endif
    </div>
@endsection

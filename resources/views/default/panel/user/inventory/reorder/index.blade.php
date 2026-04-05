@extends('panel.layout.app')
@section('title', __('Reorder Recommendations'))

@section('titlebar_actions')
    <form method="POST" action="{{ route('dashboard.inventory.reorder.scan') }}">
        @csrf
        <x-button type="submit">{{ __('Scan for Low Stock') }}</x-button>
    </form>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        @if(count($recommendations) === 0)
            <p class="text-gray-500">{{ __('No reorder recommendations at this time.') }}</p>
        @else
            <x-table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('Item') }}</th>
                        <th>{{ __('SKU') }}</th>
                        <th>{{ __('On Hand') }}</th>
                        <th>{{ __('Reorder Point') }}</th>
                        <th>{{ __('Open PO Qty') }}</th>
                        <th>{{ __('Suggested Qty') }}</th>
                        <th>{{ __('Preferred Supplier') }}</th>
                        <th>{{ __('Est. Cost') }}</th>
                        <th>{{ __('Priority') }}</th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @foreach($recommendations as $rec)
                        <tr>
                            <td>{{ $rec['item_name'] }}</td>
                            <td>{{ $rec['sku'] }}</td>
                            <td>{{ $rec['qty_on_hand'] }}</td>
                            <td>{{ $rec['reorder_point'] }}</td>
                            <td>{{ $rec['open_po_qty'] }}</td>
                            <td>{{ $rec['suggested_order_qty'] }}</td>
                            <td>{{ $rec['preferred_supplier'] ?? '—' }}</td>
                            <td>{{ number_format($rec['estimated_cost'], 2) }}</td>
                            <td>
                                @if($rec['priority'] === 'critical')
                                    <span class="text-red-600 font-semibold">{{ __('Critical') }}</span>
                                @elseif($rec['priority'] === 'high')
                                    <span class="text-orange-500 font-semibold">{{ __('High') }}</span>
                                @else
                                    <span class="text-yellow-500">{{ __('Medium') }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-table>
        @endif
    </div>
@endsection

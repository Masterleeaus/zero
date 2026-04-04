@extends('panel.layout.app')
@section('title', __('Supplier Bills'))

@section('content')
    <div class="py-6 space-y-4">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-semibold">{{ __('Supplier Bills') }}</h1>
            <x-button href="{{ route('dashboard.money.supplier-bills.create') }}">{{ __('New Bill') }}</x-button>
        </div>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Bill #') }}</th>
                    <th>{{ __('Supplier') }}</th>
                    <th>{{ __('Bill Date') }}</th>
                    <th>{{ __('Due Date') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Total') }}</th>
                    <th class="text-end">{{ __('Balance Due') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($bills as $bill)
                    <tr>
                        <td><a href="{{ route('dashboard.money.supplier-bills.show', $bill) }}" class="text-blue-600 hover:underline">{{ $bill->bill_number }}</a></td>
                        <td>{{ $bill->supplier?->name ?? '—' }}</td>
                        <td>{{ $bill->bill_date?->format('Y-m-d') }}</td>
                        <td>
                            {{ $bill->due_date?->format('Y-m-d') }}
                            @if($bill->isOverdue())
                                <x-badge variant="danger">{{ __('Overdue') }}</x-badge>
                            @endif
                        </td>
                        <td>
                            @php
                                $variant = match($bill->status) {
                                    'approved' => 'primary',
                                    'paid'     => 'success',
                                    'cancelled'=> 'danger',
                                    default    => 'warning',
                                };
                            @endphp
                            <x-badge variant="{{ $variant }}">{{ ucfirst($bill->status) }}</x-badge>
                        </td>
                        <td class="text-end">{{ number_format($bill->total_amount, 2) }}</td>
                        <td class="text-end">{{ number_format($bill->balanceDue(), 2) }}</td>
                        <td class="text-end">
                            <a href="{{ route('dashboard.money.supplier-bills.show', $bill) }}" class="text-blue-600 hover:underline text-sm">{{ __('View') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-gray-500 py-4">{{ __('No supplier bills found.') }}</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $bills->links() }}
    </div>
@endsection

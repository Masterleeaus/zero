@extends('panel.layout.app')
@section('title', __('Supplier Bill') . ' ' . $bill->bill_number)

@section('content')
    <div class="py-6 space-y-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-xl font-semibold">{{ __('Supplier Bill') }}: {{ $bill->bill_number }}</h1>
                <p class="text-gray-500 text-sm">{{ $bill->supplier?->name }}</p>
            </div>
            <div class="flex gap-2">
                @if($bill->isDraft())
                    <form method="post" action="{{ route('dashboard.money.supplier-bills.approve', $bill) }}">
                        @csrf
                        <x-button type="submit" variant="primary">{{ __('Approve') }}</x-button>
                    </form>
                @endif
                <x-button variant="secondary" href="{{ route('dashboard.money.supplier-bills.index') }}">{{ __('Back') }}</x-button>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4 bg-gray-50 p-4 rounded">
            <div>
                <p class="text-xs text-gray-500">{{ __('Bill Date') }}</p>
                <p class="font-medium">{{ $bill->bill_date?->format('Y-m-d') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">{{ __('Due Date') }}</p>
                <p class="font-medium">{{ $bill->due_date?->format('Y-m-d') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">{{ __('Status') }}</p>
                @php
                    $variant = match($bill->status) {
                        'approved' => 'primary',
                        'paid'     => 'success',
                        'cancelled'=> 'danger',
                        default    => 'warning',
                    };
                @endphp
                <x-badge variant="{{ $variant }}">{{ ucfirst($bill->status) }}</x-badge>
                @if($bill->isOverdue())
                    <x-badge variant="danger">{{ __('Overdue') }}</x-badge>
                @endif
            </div>
        </div>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Description') }}</th>
                    <th class="text-end">{{ __('Qty') }}</th>
                    <th class="text-end">{{ __('Unit Price') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @foreach($bill->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="text-end">{{ $item->quantity }}</td>
                        <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="font-semibold">
                    <td colspan="3" class="text-end">{{ __('Total') }}</td>
                    <td class="text-end">{{ number_format($bill->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end text-gray-500">{{ __('Amount Paid') }}</td>
                    <td class="text-end text-green-600">{{ number_format($bill->amount_paid, 2) }}</td>
                </tr>
                <tr class="font-semibold">
                    <td colspan="3" class="text-end">{{ __('Balance Due') }}</td>
                    <td class="text-end text-red-600">{{ number_format($bill->balanceDue(), 2) }}</td>
                </tr>
            </x-slot:body>
        </x-table>

        @if($bill->isApproved() && $bill->balanceDue() > 0)
            <div class="border-t pt-4">
                <h2 class="font-medium mb-2">{{ __('Record Payment') }}</h2>
                <form method="post" action="{{ route('dashboard.money.supplier-bills.payment', $bill) }}" class="flex gap-2 items-end">
                    @csrf
                    <x-form.group>
                        <x-form.label for="amount">{{ __('Amount') }}</x-form.label>
                        <x-form.input type="number" id="amount" name="amount" value="{{ $bill->balanceDue() }}" min="0.01" step="0.01" />
                    </x-form.group>
                    <x-button type="submit">{{ __('Record Payment') }}</x-button>
                </form>
            </div>
        @endif
    </div>
@endsection

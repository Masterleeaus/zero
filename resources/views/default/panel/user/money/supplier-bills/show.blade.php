@extends('panel.layout.app')
@section('title', __('Bill: :ref', ['ref' => $bill->reference ?: 'BILL-' . $bill->id]))
@section('titlebar_actions')
    @can('update', $bill)
        <x-button href="{{ route('dashboard.money.supplier-bills.edit', $bill) }}" variant="secondary">
            {{ __('Edit') }}
        </x-button>
    @endcan
@endsection

@section('content')
    <div class="py-6 space-y-6">
        {{-- Header details --}}
        <x-card>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <div class="text-sm text-slate-500">{{ __('Supplier') }}</div>
                    <a href="{{ route('dashboard.money.suppliers.show', $bill->supplier) }}" class="hover:underline font-medium">
                        {{ $bill->supplier?->name }}
                    </a>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Status') }}</div>
                    <x-badge variant="{{ match($bill->status) {
                        'paid'     => 'success',
                        'overdue'  => 'danger',
                        'awaiting_payment', 'partial' => 'warning',
                        default    => 'secondary',
                    } }}">{{ ucfirst(str_replace('_', ' ', $bill->status)) }}</x-badge>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Reference') }}</div>
                    <div>{{ $bill->reference ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Bill Date') }}</div>
                    <div>{{ optional($bill->bill_date)->toFormattedDateString() }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Due Date') }}</div>
                    <div>{{ optional($bill->due_date)->toFormattedDateString() ?: '—' }}</div>
                </div>
                @if($bill->purchaseOrder)
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Purchase Order') }}</div>
                        <a href="{{ route('dashboard.money.purchase-orders.show', $bill->purchaseOrder) }}" class="hover:underline">
                            {{ $bill->purchaseOrder->po_number }}
                        </a>
                    </div>
                @endif
                <div>
                    <div class="text-sm text-slate-500">{{ __('Subtotal') }}</div>
                    <div>{{ number_format($bill->subtotal, 2) }} {{ $bill->currency }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Tax') }}</div>
                    <div>{{ number_format($bill->tax_total, 2) }} {{ $bill->currency }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Total') }}</div>
                    <div class="font-semibold text-lg">{{ number_format($bill->total, 2) }} {{ $bill->currency }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Paid') }}</div>
                    <div>{{ number_format($bill->amount_paid, 2) }} {{ $bill->currency }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Balance') }}</div>
                    <div class="font-semibold {{ $bill->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format($bill->balance, 2) }} {{ $bill->currency }}
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Line Items --}}
        @if($bill->lines->count())
            <x-card title="{{ __('Line Items') }}">
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th>{{ __('Account') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Tax') }}</th>
                            <th>{{ __('Job ID') }}</th>
                        </tr>
                    </x-slot:head>
                    @foreach($bill->lines as $line)
                        <tr>
                            <td>{{ $line->account?->code }} {{ $line->account?->name }}</td>
                            <td>{{ $line->description }}</td>
                            <td>{{ number_format($line->amount, 2) }}</td>
                            <td>{{ number_format($line->tax_amount, 2) }}</td>
                            <td>{{ $line->service_job_id ?: '—' }}</td>
                        </tr>
                    @endforeach
                </x-table>
            </x-card>
        @endif

        {{-- Payments --}}
        @if($bill->payments->count())
            <x-card title="{{ __('Payments') }}">
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Reference') }}</th>
                            <th>{{ __('Account') }}</th>
                            <th>{{ __('Amount') }}</th>
                        </tr>
                    </x-slot:head>
                    @foreach($bill->payments as $payment)
                        <tr>
                            <td>{{ optional($payment->payment_date)->toFormattedDateString() }}</td>
                            <td>{{ $payment->reference ?: '—' }}</td>
                            <td>{{ $payment->paymentAccount?->name ?: '—' }}</td>
                            <td>{{ number_format($payment->amount, 2) }} {{ $bill->currency }}</td>
                        </tr>
                    @endforeach
                </x-table>
            </x-card>
        @endif

        {{-- Record Payment --}}
        @can('recordPayment', $bill)
            <x-card title="{{ __('Record Payment') }}">
                <form method="post" action="{{ route('dashboard.money.supplier-payments.store', $bill) }}">
                    @csrf
                    <div class="grid md:grid-cols-3 gap-4">
                        <x-input name="amount" type="number" step="0.01" label="{{ __('Amount') }}" value="{{ old('amount', $bill->balance) }}" required />
                        <x-input name="payment_date" type="date" label="{{ __('Payment Date') }}" value="{{ old('payment_date', now()->toDateString()) }}" required />
                        <x-input name="reference" label="{{ __('Reference') }}" value="{{ old('reference') }}" />
                    </div>
                    <div class="mt-4">
                        <x-button type="submit">
                            <x-tabler-cash class="size-4" />
                            {{ __('Record Payment') }}
                        </x-button>
                    </div>
                </form>
            </x-card>
        @endcan
    </div>
@endsection

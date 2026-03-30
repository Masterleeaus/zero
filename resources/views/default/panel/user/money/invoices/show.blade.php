@extends('panel.layout.app')
@section('title', __('Invoice'))

@section('content')
    <div class="py-6 space-y-4">
        <x-card>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-slate-500">{{ __('Number') }}</div>
                    <div class="text-lg font-semibold">{{ $invoice->invoice_number }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Status') }}</div>
                    <x-badge variant="info">{{ ucfirst($invoice->status) }}</x-badge>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Customer') }}</div>
                    <div>{{ $invoice->customer?->name }}</div>
                </div>
                @if($invoice->quote)
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Quote') }}</div>
                        <div>{{ $invoice->quote->quote_number }}</div>
                    </div>
                @endif
                <div>
                    <div class="text-sm text-slate-500">{{ __('Total') }}</div>
                    <div>{{ $invoice->total }} {{ $invoice->currency }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Paid') }}</div>
                    <div>{{ $invoice->paid_amount }} {{ $invoice->currency }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Balance') }}</div>
                    <div>{{ $invoice->balance }} {{ $invoice->currency }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Issue Date') }}</div>
                    <div>{{ optional($invoice->issue_date)->toFormattedDateString() }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Due Date') }}</div>
                    <div>{{ optional($invoice->due_date)->toFormattedDateString() }}</div>
                </div>
            </div>

            @if($invoice->notes)
                <div class="mt-4">
                    <div class="text-sm text-slate-500">{{ __('Notes') }}</div>
                    <p class="whitespace-pre-line">{{ $invoice->notes }}</p>
                </div>
            @endif
        </x-card>

        <x-card>
            <div class="font-semibold mb-3">{{ __('Line Items') }}</div>
            <x-table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Qty') }}</th>
                        <th>{{ __('Unit Price') }}</th>
                        <th>{{ __('Tax %') }}</th>
                        <th>{{ __('Line Total') }}</th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @forelse($invoice->items as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->unit_price }}</td>
                            <td>{{ $item->tax_rate }}</td>
                            <td>{{ $item->line_total }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-slate-500 py-4">{{ __('No items') }}</td>
                        </tr>
                    @endforelse
                </x-slot:body>
            </x-table>
        </x-card>

        <x-card>
            <div class="flex justify-between items-center mb-3">
                <div class="font-semibold">{{ __('Payments') }}</div>
                <div class="space-x-2">
                    <form method="post" action="{{ route('dashboard.money.invoices.mark-paid', $invoice) }}" class="inline">
                        @csrf
                        <x-button type="submit" variant="secondary">{{ __('Mark Paid') }}</x-button>
                    </form>
                    <form method="post" action="{{ route('dashboard.money.invoices.mark-overdue', $invoice) }}" class="inline">
                        @csrf
                        <x-button type="submit" variant="ghost">{{ __('Mark Overdue') }}</x-button>
                    </form>
                    <x-button href="{{ route('dashboard.money.invoices.edit', $invoice) }}" variant="ghost">
                        {{ __('Edit') }}
                    </x-button>
                </div>
            </div>
            <x-table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Method') }}</th>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Paid At') }}</th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @forelse($invoice->payments as $payment)
                        <tr>
                            <td>{{ $payment->amount }}</td>
                            <td>{{ $payment->method }}</td>
                            <td>{{ $payment->reference }}</td>
                            <td>{{ optional($payment->paid_at)->toDayDateTimeString() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-slate-500 py-4">{{ __('No payments yet') }}</td>
                        </tr>
                    @endforelse
                </x-slot:body>
            </x-table>
        </x-card>

        <x-card>
            <div class="font-semibold mb-3">{{ __('Record Payment') }}</div>
            <form method="post" action="{{ route('dashboard.money.payments.store', $invoice) }}" class="grid md:grid-cols-3 gap-3">
                @csrf
                <x-input name="amount" type="number" step="0.01" label="{{ __('Amount') }}" required />
                <x-input name="method" label="{{ __('Method') }}" />
                <x-input name="reference" label="{{ __('Reference') }}" />
                <x-input name="paid_at" type="datetime-local" label="{{ __('Paid at') }}" />
                <div class="md:col-span-3">
                    <x-button type="submit">
                        <x-tabler-cash class="size-4" />
                        {{ __('Save Payment') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection

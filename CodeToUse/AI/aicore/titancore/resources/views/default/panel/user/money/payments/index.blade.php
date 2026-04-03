@extends('panel.layout.app')
@section('title', __('Payments'))

@section('titlebar_actions')
    <x-button href="{{ route('dashboard.money.invoices.index') }}" variant="ghost">
        <x-tabler-file-invoice class="size-4" />
        {{ __('Go to Invoices') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <x-card>
            <x-table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('Invoice') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Method') }}</th>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Paid At') }}</th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @forelse($payments as $payment)
                        <tr>
                            <td>
                                @if($payment->invoice)
                                    <a href="{{ route('dashboard.money.invoices.show', $payment->invoice) }}" class="text-primary-600 hover:underline">
                                        {{ $payment->invoice->invoice_number ?? __('Invoice') . ' #' . $payment->invoice->id }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $payment->amount }}</td>
                            <td>{{ $payment->method }}</td>
                            <td>{{ $payment->reference }}</td>
                            <td>{{ optional($payment->paid_at)->toDayDateTimeString() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-slate-500 py-6">{{ __('No payments recorded.') }}</td>
                        </tr>
                    @endforelse
                </x-slot:body>
            </x-table>

            <div class="mt-4">
                {{ $payments->links() }}
            </div>
        </x-card>
    </div>
@endsection

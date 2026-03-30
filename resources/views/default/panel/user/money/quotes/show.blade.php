@extends('panel.layout.app')
@section('title', __('Quote'))

@section('content')
    <div class="py-6 space-y-4">
        <x-card>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-slate-500">{{ __('Number') }}</div>
                    <div class="text-lg font-semibold">{{ $quote->quote_number }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Status') }}</div>
                    <x-badge variant="info">{{ ucfirst($quote->status) }}</x-badge>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Customer') }}</div>
                    <div>{{ $quote->customer?->name }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Total') }}</div>
                    <div>{{ $quote->total }} {{ $quote->currency }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Issue Date') }}</div>
                    <div>{{ optional($quote->issue_date)->toFormattedDateString() }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Due Date') }}</div>
                    <div>{{ optional($quote->due_date)->toFormattedDateString() }}</div>
                </div>
            </div>

            @if($quote->notes)
                <div class="mt-4">
                    <div class="text-sm text-slate-500">{{ __('Notes') }}</div>
                    <p class="whitespace-pre-line">{{ $quote->notes }}</p>
                </div>
            @endif
        </x-card>

        <x-card>
            <div class="flex justify-between items-center mb-3">
                <div class="font-semibold">{{ __('Invoices') }}</div>
                <div class="space-x-2">
                    <form method="post" action="{{ route('dashboard.money.quotes.status', $quote) }}" class="inline">
                        @csrf
                        <input type="hidden" name="status" value="accepted">
                        <x-button type="submit" variant="secondary">{{ __('Mark Accepted') }}</x-button>
                    </form>
                    <x-button href="{{ route('dashboard.money.quotes.edit', $quote) }}" variant="ghost">
                        {{ __('Edit') }}
                    </x-button>
                </div>
            </div>
            <div class="font-semibold mb-3">{{ __('Invoices') }}</div>
            <x-table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('Number') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th class="text-end">{{ __('Action') }}</th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @forelse($quote->invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_number }}</td>
                            <td><x-badge variant="info">{{ ucfirst($invoice->status) }}</x-badge></td>
                            <td>{{ $invoice->total }} {{ $invoice->currency }}</td>
                            <td class="text-end whitespace-nowrap">
                                <x-button variant="ghost-shadow" size="none" class="size-9"
                                          href="{{ route('dashboard.money.invoices.show', $invoice) }}">
                                    <x-tabler-eye class="size-4" />
                                </x-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-slate-500 py-4">{{ __('No invoices yet') }}</td>
                        </tr>
                    @endforelse
                </x-slot:body>
            </x-table>
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
                    @forelse($quote->items as $item)
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
            <div class="font-semibold mb-3">{{ __('Convert to Service Job') }}</div>
            <form method="post" action="{{ route('dashboard.money.quotes.convert-job', $quote) }}" class="grid md:grid-cols-3 gap-3">
                @csrf
                <x-select name="site_id" label="{{ __('Site') }}" required>
                    @foreach($sites as $site)
                        <option value="{{ $site->id }}" @selected($quote->site_id == $site->id)>{{ $site->name }}</option>
                    @endforeach
                </x-select>
                <div class="md:col-span-3">
                    <x-button type="submit">
                        <x-tabler-briefcase class="size-4" />
                        {{ __('Create Service Job') }}
                    </x-button>
                </div>
            </form>
        </x-card>

        <x-card>
            <div class="font-semibold mb-3">{{ __('Create Invoice from Quote') }}</div>
            @if($quote->status === 'accepted')
                <form method="post" action="{{ route('dashboard.money.quotes.convert-invoice', $quote) }}">
                    @csrf
                    <x-button type="submit">
                        <x-tabler-file-invoice class="size-4" />
                        {{ __('Generate Invoice') }}
                    </x-button>
                </form>
            @else
                <p class="text-slate-500 text-sm">{{ __('Quote must be accepted before invoicing.') }}</p>
            @endif
        </x-card>
    </div>
@endsection

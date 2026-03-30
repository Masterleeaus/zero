@extends('panel.layout.app')
@section('title', __('Invoice'))

@section('content')
    <div class="py-6 space-y-4">
        <x-card>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-slate-500">{{ __('Number') }}</div>
                    <div class="text-lg font-semibold">{{ $invoice->number }}</div>
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
                        <div>{{ $invoice->quote->number }}</div>
                    </div>
                @endif
                <div>
                    <div class="text-sm text-slate-500">{{ __('Total') }}</div>
                    <div>{{ $invoice->total }} {{ $invoice->currency }}</div>
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
    </div>
@endsection

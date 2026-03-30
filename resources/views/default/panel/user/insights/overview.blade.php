@extends('panel.layout.app')
@section('title', __('Insights Overview'))

@section('content')
    <div class="py-6 space-y-6">
        <div class="space-y-2">
            <div class="text-sm uppercase tracking-wide text-slate-500">{{ __('CRM') }}</div>
            <div class="grid md:grid-cols-3 gap-4">
                <x-card class="space-y-1">
                    <div class="text-sm text-slate-500">{{ __('Enquiries') }}</div>
                    <div class="text-2xl font-semibold">{{ $enquiryCount }}</div>
                </x-card>
                <x-card class="space-y-1">
                    <div class="text-sm text-slate-500">{{ __('Customers') }}</div>
                    <div class="text-2xl font-semibold">{{ $customerCount }}</div>
                </x-card>
                <x-card class="space-y-1">
                    <div class="text-sm text-slate-500">{{ __('Active Sites') }}</div>
                    <div class="text-2xl font-semibold">{{ $activeSites }}</div>
                </x-card>
            </div>
        </div>

        <div class="space-y-2">
            <div class="text-sm uppercase tracking-wide text-slate-500">{{ __('Work & Money') }}</div>
            <div class="grid md:grid-cols-2 gap-4">
                <x-card>
                    <div class="font-semibold mb-3">{{ __('Service Jobs by Status') }}</div>
                    <div class="space-y-2">
                        @forelse($jobStatus as $status => $total)
                            <div class="flex justify-between">
                                <span class="text-slate-600">{{ ucfirst(str_replace('_',' ',$status)) }}</span>
                                <span class="font-semibold">{{ $total }}</span>
                            </div>
                        @empty
                            <p class="text-slate-500">{{ __('No jobs yet') }}</p>
                        @endforelse
                    </div>
                </x-card>
                <x-card>
                    <div class="font-semibold mb-3">{{ __('Quotes by Status') }}</div>
                    <div class="space-y-2">
                        @forelse($quoteStatus as $status => $total)
                            <div class="flex justify-between">
                                <span class="text-slate-600">{{ ucfirst($status) }}</span>
                                <span class="font-semibold">{{ $total }}</span>
                            </div>
                        @empty
                            <p class="text-slate-500">{{ __('No quotes yet') }}</p>
                        @endforelse
                    </div>
                </x-card>
            </div>
        </div>

        <div class="space-y-2">
            <div class="text-sm uppercase tracking-wide text-slate-500">{{ __('Invoices & Conversions') }}</div>
            <div class="grid md:grid-cols-2 gap-4">
                <x-card>
                    <div class="font-semibold mb-3">{{ __('Invoices by Status') }}</div>
                    <div class="space-y-2">
                        @forelse($invoiceStatus as $status => $total)
                            <div class="flex justify-between">
                                <span class="text-slate-600">{{ ucfirst($status) }}</span>
                                <span class="font-semibold">{{ $total }}</span>
                            </div>
                        @empty
                            <p class="text-slate-500">{{ __('No invoices yet') }}</p>
                        @endforelse
                    </div>
                </x-card>
                <x-card class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('Overdue Invoices') }}</span>
                        <span class="font-semibold">{{ $overdueInvoices }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('Outstanding Balance') }}</span>
                        <span class="font-semibold">{{ number_format($outstandingBalance, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('Payments Total') }}</span>
                        <span class="font-semibold">{{ number_format($paymentsTotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('Quote → Job Conversions') }}</span>
                        <span class="font-semibold">{{ $quoteToJobCount }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('Quote → Invoice Conversions') }}</span>
                        <span class="font-semibold">{{ $quoteToInvoiceCount }}</span>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
@endsection

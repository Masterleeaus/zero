@extends('panel.layout.app')
@section('title', __('Insights Overview'))

@section('content')
    <div class="py-6 space-y-6">
        <div class="space-y-2">
            <div class="text-sm uppercase tracking-wide text-slate-500">{{ __('CRM & Support') }}</div>
            <div class="grid md:grid-cols-3 gap-4">
                <x-card>
                    <div class="font-semibold mb-2">{{ __('Enquiries (open)') }}</div>
                    <div class="text-2xl font-bold">{{ $enquiryCount }}</div>
                    <div class="text-sm text-slate-500">{{ __('Recent customers') }}</div>
                    <ul class="text-sm">
                        @forelse($recentCustomers as $customer)
                            <li>{{ $customer->name }}</li>
                        @empty
                            <li class="text-slate-400">{{ __('No recent customers') }}</li>
                        @endforelse
                    </ul>
                </x-card>
                <x-card>
                    <div class="font-semibold mb-2">{{ __('Support') }}</div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('Open') }}</span>
                        <span class="font-semibold">{{ $supportOpen }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('Waiting on team') }}</span>
                        <span class="font-semibold">{{ $supportWaitingTeam }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('Waiting on user') }}</span>
                        <span class="font-semibold">{{ $supportWaitingUser }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('Resolved') }}</span>
                        <span class="font-semibold">{{ $supportResolved }}</span>
                    </div>
                </x-card>
                <x-card>
                    <div class="font-semibold mb-2">{{ __('Notifications') }}</div>
                    <div class="text-2xl font-bold">{{ $unreadNotifications }}</div>
                    <div class="text-sm text-slate-500">{{ __('Unread notifications') }}</div>
                </x-card>
            </div>
        </div>

        <div class="space-y-2">
            <div class="text-sm uppercase tracking-wide text-slate-500">{{ __('Work Summary') }}</div>
            <div class="grid md:grid-cols-4 gap-4">
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
                        <div class="flex justify-between">
                            <span class="text-slate-500">{{ __('Upcoming Jobs') }}</span>
                            <span class="font-semibold">{{ $upcomingJobs }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">{{ __('Unassigned Jobs') }}</span>
                            <span class="font-semibold">{{ $unassignedJobs }}</span>
                        </div>
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
                <x-card class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('Logged Hours (total)') }}</span>
                        <span class="font-semibold">{{ $timelogHours }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('Attendance open') }}</span>
                        <span class="font-semibold">{{ $attendanceOpen }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('Active Agreements') }}</span>
                        <span class="font-semibold">{{ $agreementsActive }}</span>
                    </div>
                </x-card>
                <x-card class="space-y-2">
                    <div class="font-semibold">{{ __('Leave Summary') }}</div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('Total leave records') }}</span>
                        <span class="font-semibold">{{ $leaveTotals }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('Upcoming leave') }}</span>
                        <span class="font-semibold">{{ $upcomingLeave }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('Leave vs shift conflicts') }}</span>
                        <span class="font-semibold">{{ $leaveShiftConflicts }}</span>
                    </div>
                </x-card>
                <x-card class="space-y-2">
                    <div class="font-semibold">{{ __('Expenses') }}</div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('Total Spend') }}</span>
                        <span class="font-semibold">{{ number_format($expenseTotal, 2) }}</span>
                    </div>
                    <div class="space-y-1">
                        @forelse($expenseByCategory as $categoryId => $total)
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">{{ __('Category') }} #{{ $categoryId }}</span>
                                <span class="font-semibold">{{ number_format($total, 2) }}</span>
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm">{{ __('No expenses yet') }}</p>
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
                        <span class="text-slate-500">{{ __('Payments Received') }}</span>
                        <span class="font-semibold">{{ number_format($paymentsTotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('Quote → Job') }}</span>
                        <span class="font-semibold">{{ $quoteToJobCount }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('Quote → Invoice') }}</span>
                        <span class="font-semibold">{{ $quoteToInvoiceCount }}</span>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
@endsection

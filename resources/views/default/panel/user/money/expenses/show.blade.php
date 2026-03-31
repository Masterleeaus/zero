@extends('panel.layout.app')
@section('title', __('Expense'))

@section('content')
    <div class="py-6 space-y-4">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-semibold">{{ $expense->title }}</h1>
            <x-button variant="secondary" href="{{ route('dashboard.money.expenses.index') }}">{{ __('Back') }}</x-button>
        </div>

        @if(session('message'))
            <div class="p-4 bg-green-50 text-green-700 rounded">{{ session('message') }}</div>
        @endif

        <div class="bg-white rounded shadow p-6 space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-slate-500">{{ __('Category') }}</div>
                    <div>{{ $expense->category?->name ?? __('Uncategorised') }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Amount') }}</div>
                    <div>{{ number_format($expense->amount, 2) }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Date') }}</div>
                    <div>{{ $expense->expense_date?->format('Y-m-d') ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Submitted by') }}</div>
                    <div>{{ $expense->createdBy?->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Status') }}</div>
                    <div>
                        @if($expense->status === 'approved')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">{{ __('Approved') }}</span>
                        @elseif($expense->status === 'rejected')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">{{ __('Rejected') }}</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">{{ __('Pending') }}</span>
                        @endif
                    </div>
                </div>
                @if($expense->status !== 'pending')
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Reviewed by') }}</div>
                        <div>{{ $expense->approvedBy?->name ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Reviewed at') }}</div>
                        <div>{{ $expense->approved_at?->format('Y-m-d H:i') ?? '—' }}</div>
                    </div>
                @endif
            </div>

            @if($expense->notes)
                <div>
                    <div class="text-sm text-slate-500">{{ __('Notes') }}</div>
                    <div class="whitespace-pre-line">{{ $expense->notes }}</div>
                </div>
            @endif

            @if($expense->status === 'rejected' && $expense->rejection_reason)
                <div class="p-4 bg-red-50 text-red-700 rounded">
                    <div class="font-medium">{{ __('Rejection reason') }}</div>
                    <div class="whitespace-pre-line">{{ $expense->rejection_reason }}</div>
                </div>
            @endif
        </div>

        @if(auth()->user()->isAdmin() && $expense->isPending())
            <div class="flex gap-4 items-start">
                <form method="post" action="{{ route('dashboard.money.expenses.approve', $expense) }}">
                    @csrf
                    <x-button type="submit" variant="primary">{{ __('Approve') }}</x-button>
                </form>

                <form method="post" action="{{ route('dashboard.money.expenses.reject', $expense) }}" class="space-y-2 flex-1 max-w-md">
                    @csrf
                    <div>
                        <label for="rejection_reason" class="block text-sm font-medium text-slate-700">{{ __('Rejection reason') }}</label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="2"
                            class="mt-1 block w-full rounded border-slate-300 shadow-sm text-sm @error('rejection_reason') border-red-500 @enderror"
                            required>{{ old('rejection_reason') }}</textarea>
                        @error('rejection_reason')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <x-button type="submit" variant="danger">{{ __('Reject') }}</x-button>
                </form>
            </div>
        @endif
    </div>
@endsection

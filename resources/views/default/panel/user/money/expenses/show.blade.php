@extends('panel.layout.app')
@section('title', __('Expense'))

@section('content')
    <div class="py-6 space-y-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold">{{ $expense->title }}</h1>
            <div class="space-x-2">
                <x-button variant="ghost" href="{{ route('dashboard.money.expenses.edit', $expense) }}">
                    {{ __('Edit') }}
                </x-button>
                <x-button variant="secondary" href="{{ route('dashboard.money.expenses.index') }}">
                    {{ __('Back') }}
                </x-button>
            </div>
        </div>

        <x-card>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <div class="text-sm text-slate-500">{{ __('Status') }}</div>
                    @php
                        $variant = match ($expense->status) {
                            'approved' => 'success',
                            'rejected' => 'danger',
                            default => 'warning',
                        };
                    @endphp
                    <x-badge variant="{{ $variant }}">{{ ucfirst($expense->status) }}</x-badge>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Category') }}</div>
                    <div>{{ $expense->category?->name ?? __('Uncategorised') }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Date') }}</div>
                    <div>{{ optional($expense->expense_date)->toDateString() }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Amount') }}</div>
                    <div>{{ number_format($expense->amount, 2) }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Created By') }}</div>
                    <div>{{ $expense->createdBy?->name ?? __('Unknown') }}</div>
                </div>
                @if($expense->status === 'approved')
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Approved By') }}</div>
                        <div>{{ $expense->approver?->name ?? __('Unknown') }}</div>
                        <div class="text-xs text-slate-500">
                            {{ optional($expense->approved_at)->toDayDateTimeString() }}
                        </div>
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
            @if($expense->status === 'rejected' && $expense->rejection_reason)
                <div class="mt-4">
                    <div class="text-sm text-slate-500">{{ __('Rejection Reason') }}</div>
                    <p class="whitespace-pre-line">{{ $expense->rejection_reason }}</p>
                </div>
            @endif

            @if($expense->notes)
                <div class="mt-4">
                    <div class="text-sm text-slate-500">{{ __('Notes') }}</div>
                    <p class="whitespace-pre-line">{{ $expense->notes }}</p>
                </div>
            @endif
        </x-card>

        @can('update', $expense)
            @if($expense->status === 'pending')
                <x-card>
                    <div class="flex items-start gap-4">
                        <form method="post" action="{{ route('dashboard.money.expenses.approve', $expense) }}">
                            @csrf
                            <x-button type="submit" variant="success">
                                {{ __('Approve') }}
                            </x-button>
                        </form>
                        <form method="post" action="{{ route('dashboard.money.expenses.reject', $expense) }}" class="space-y-2 flex-1">
                            @csrf
                            <x-form.textarea name="reason" rows="2" placeholder="{{ __('Reason (optional)') }}"></x-form.textarea>
                            <x-button type="submit" variant="danger">
                                {{ __('Reject') }}
                            </x-button>
                        </form>
                    </div>
                </x-card>
            @endif
        @endcan
    </div>
@endsection

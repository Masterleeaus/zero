@extends('panel.layout.app')
@section('title', __('Edit Expense'))

@section('content')
    <div class="py-6 space-y-4">
        <h2 class="text-lg font-semibold">{{ __('Edit Expense') }}</h2>
        <form method="post" action="{{ route('dashboard.money.expenses.update', $expense) }}" class="space-y-4">
            @method('put')
            @include('default.panel.user.money.expenses.form')
            <x-button type="submit">{{ __('Update') }}</x-button>
        </form>
    </div>
@endsection

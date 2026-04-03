@extends('panel.layout.app')
@section('title', __('Create Expense'))

@section('content')
    <div class="py-6 space-y-4">
        <h2 class="text-lg font-semibold">{{ __('Create Expense') }}</h2>
        <form method="post" action="{{ route('dashboard.money.expenses.store') }}" class="space-y-4">
            @include('default.panel.user.money.expenses.form')
            <x-button type="submit">{{ __('Save') }}</x-button>
        </form>
    </div>
@endsection

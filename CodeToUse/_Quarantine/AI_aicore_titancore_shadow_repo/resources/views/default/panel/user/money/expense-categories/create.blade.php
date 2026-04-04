@extends('panel.layout.app')
@section('title', __('Create Category'))

@section('content')
    <div class="py-6 space-y-4">
        <h2 class="text-lg font-semibold">{{ __('Create Expense Category') }}</h2>
        <form method="post" action="{{ route('dashboard.money.expense-categories.store') }}" class="space-y-4">
            @csrf
            @include('default.panel.user.money.expense-categories.form')
            <x-button type="submit">{{ __('Save') }}</x-button>
        </form>
    </div>
@endsection

@extends('panel.layout.app')
@section('title', __('Edit Category'))

@section('content')
    <div class="py-6 space-y-4">
        <h2 class="text-lg font-semibold">{{ __('Edit Expense Category') }}</h2>
        <form method="post" action="{{ route('dashboard.money.expense-categories.update', $category) }}" class="space-y-4">
            @csrf
            @method('put')
            @include('default.panel.user.money.expense-categories.form')
            <x-button type="submit">{{ __('Update') }}</x-button>
        </form>
    </div>
@endsection

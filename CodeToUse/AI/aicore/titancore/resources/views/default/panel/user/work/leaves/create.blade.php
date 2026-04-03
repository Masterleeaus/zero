@extends('panel.layout.app')
@section('title', __('Create Leave'))

@section('content')
    <div class="py-6 space-y-4">
        <h2 class="text-lg font-semibold">{{ __('Create Leave') }}</h2>
        <form method="post" action="{{ route('dashboard.work.leaves.store') }}" class="space-y-4">
            @include('default.panel.user.work.leaves.form', ['leave' => null])
            <x-button type="submit">{{ __('Save') }}</x-button>
        </form>
    </div>
@endsection

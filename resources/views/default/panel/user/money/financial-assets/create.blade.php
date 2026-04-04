@extends('panel.layout.app')
@section('title', __('Register Financial Asset'))

@section('content')
    <div class="py-6 max-w-xl">
        <h1 class="text-xl font-semibold mb-4">{{ __('Register Financial Asset') }}</h1>

        <form method="post" action="{{ route('dashboard.money.financial-assets.store') }}" class="space-y-4">
            @csrf
            @include('default.panel.user.money.financial-assets.form')
            <div class="flex gap-2">
                <x-button type="submit">{{ __('Register Asset') }}</x-button>
                <x-button variant="secondary" href="{{ route('dashboard.money.financial-assets.index') }}">{{ __('Cancel') }}</x-button>
            </div>
        </form>
    </div>
@endsection

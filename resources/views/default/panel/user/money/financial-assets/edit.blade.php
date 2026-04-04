@extends('panel.layout.app')
@section('title', __('Edit Asset') . ': ' . $asset->name)

@section('content')
    <div class="py-6 max-w-xl">
        <h1 class="text-xl font-semibold mb-4">{{ __('Edit Asset') }}: {{ $asset->name }}</h1>

        <form method="post" action="{{ route('dashboard.money.financial-assets.update', $asset) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('default.panel.user.money.financial-assets.form')
            <div class="flex gap-2">
                <x-button type="submit">{{ __('Save Changes') }}</x-button>
                <x-button variant="secondary" href="{{ route('dashboard.money.financial-assets.show', $asset) }}">{{ __('Cancel') }}</x-button>
            </div>
        </form>
    </div>
@endsection

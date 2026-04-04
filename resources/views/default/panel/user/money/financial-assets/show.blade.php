@extends('panel.layout.app')
@section('title', $asset->name)

@section('content')
    <div class="py-6 space-y-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-xl font-semibold">{{ $asset->name }}</h1>
                <p class="text-gray-500 text-sm">{{ $asset->category }}</p>
            </div>
            <div class="flex gap-2">
                @if($asset->isActive())
                    <x-button href="{{ route('dashboard.money.financial-assets.edit', $asset) }}">{{ __('Edit') }}</x-button>
                @endif
                <x-button variant="secondary" href="{{ route('dashboard.money.financial-assets.index') }}">{{ __('Back') }}</x-button>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4 bg-gray-50 p-4 rounded">
            <div><p class="text-xs text-gray-500">{{ __('Acquired') }}</p><p class="font-medium">{{ $asset->acquisition_date?->format('Y-m-d') }}</p></div>
            <div><p class="text-xs text-gray-500">{{ __('Cost') }}</p><p class="font-medium">{{ number_format($asset->acquisition_cost, 2) }}</p></div>
            <div><p class="text-xs text-gray-500">{{ __('Current Value') }}</p><p class="font-semibold text-blue-700">{{ number_format($asset->current_value, 2) }}</p></div>
            <div><p class="text-xs text-gray-500">{{ __('Depreciation Rate (annual)') }}</p><p class="font-medium">{{ ($asset->depreciation_rate * 100) }}%</p></div>
            <div><p class="text-xs text-gray-500">{{ __('Monthly Charge') }}</p><p class="font-medium">{{ number_format($asset->monthlyDepreciationCharge(), 2) }}</p></div>
            <div>
                <p class="text-xs text-gray-500">{{ __('Status') }}</p>
                <x-badge variant="{{ $asset->isActive() ? 'success' : 'secondary' }}">{{ ucfirst($asset->status) }}</x-badge>
            </div>
        </div>

        @if($asset->description)
            <p class="text-gray-700">{{ $asset->description }}</p>
        @endif

        @if($asset->isActive())
            <div class="border-t pt-4">
                <h2 class="font-medium mb-2">{{ __('Dispose Asset') }}</h2>
                <form method="post" action="{{ route('dashboard.money.financial-assets.dispose', $asset) }}" class="flex gap-2 items-end">
                    @csrf
                    <x-form.group>
                        <x-form.label>{{ __('Disposal Date') }}</x-form.label>
                        <x-form.input type="date" name="disposal_date" value="{{ now()->toDateString() }}" required />
                    </x-form.group>
                    <x-form.group>
                        <x-form.label>{{ __('Disposal Value') }}</x-form.label>
                        <x-form.input type="number" name="disposal_value" value="0" min="0" step="0.01" required />
                    </x-form.group>
                    <x-button type="submit" variant="danger" onclick="return confirm('Dispose this asset?')">{{ __('Dispose') }}</x-button>
                </form>
            </div>
        @endif
    </div>
@endsection

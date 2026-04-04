@extends('panel.layout.app')
@section('title', __('Financial Assets'))

@section('content')
    <div class="py-6 space-y-4">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-semibold">{{ __('Financial Assets') }}</h1>
            <x-button href="{{ route('dashboard.money.financial-assets.create') }}">{{ __('Register Asset') }}</x-button>
        </div>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Category') }}</th>
                    <th>{{ __('Acquired') }}</th>
                    <th class="text-end">{{ __('Cost') }}</th>
                    <th class="text-end">{{ __('Current Value') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($assets as $asset)
                    <tr>
                        <td><a href="{{ route('dashboard.money.financial-assets.show', $asset) }}" class="text-blue-600 hover:underline">{{ $asset->name }}</a></td>
                        <td>{{ $asset->category ?? '—' }}</td>
                        <td>{{ $asset->acquisition_date?->format('Y-m-d') }}</td>
                        <td class="text-end">{{ number_format($asset->acquisition_cost, 2) }}</td>
                        <td class="text-end">{{ number_format($asset->current_value, 2) }}</td>
                        <td><x-badge variant="{{ $asset->isActive() ? 'success' : 'secondary' }}">{{ ucfirst($asset->status) }}</x-badge></td>
                        <td class="text-end">
                            <a href="{{ route('dashboard.money.financial-assets.show', $asset) }}" class="text-blue-600 hover:underline text-sm">{{ __('View') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-gray-500 py-4">{{ __('No financial assets registered.') }}</td></tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $assets->links() }}
    </div>
@endsection

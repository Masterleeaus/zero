@extends('default.layout.app')
@section('content')
    <div class="max-w-4xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Taxes') }}</p>
                <h1 class="text-2xl font-semibold">{{ __('Tax rates') }}</h1>
            </div>
            <x-button href="{{ route('dashboard.money.taxes.create') }}">
                <x-tabler-plus class="size-4" />
                {{ __('Add Tax') }}
            </x-button>
        </div>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Rate') }}</th>
                    <th>{{ __('Default') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @foreach($taxes as $tax)
                    <tr>
                        <td class="font-semibold">{{ $tax['name'] }}</td>
                        <td>{{ $tax['rate'] }}%</td>
                        <td>
                            @if($tax['default'])
                                <x-badge variant="info">{{ __('Default') }}</x-badge>
                            @else
                                <span class="text-slate-500">{{ __('Optional') }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-table>
    </div>
@endsection

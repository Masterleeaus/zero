@extends('default.layout.app')
@section('content')
    <div class="max-w-6xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Quote Templates') }}</p>
                <h1 class="text-2xl font-semibold">{{ __('Reusable layouts') }}</h1>
            </div>
            <x-button href="{{ route('dashboard.money.quote-templates.create') }}">
                <x-tabler-plus class="size-4" />
                {{ __('New Template') }}
            </x-button>
        </div>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Category') }}</th>
                    <th>{{ __('Items') }}</th>
                    <th>{{ __('Updated') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @foreach($templates as $template)
                    <tr>
                        <td class="font-semibold">{{ $template['name'] }}</td>
                        <td>{{ $template['category'] }}</td>
                        <td>{{ $template['items'] }}</td>
                        <td>{{ $template['updated_at'] }}</td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-table>
    </div>
@endsection

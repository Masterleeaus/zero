@extends('default.layout.app')
@section('content')
    @php $grouped = $deals->groupBy('stage'); @endphp
    <div class="max-w-6xl mx-auto py-10 space-y-6" x-data="{ mode: 'list' }">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Deals') }}</p>
                <h1 class="text-2xl font-semibold">{{ __('Pipeline') }}</h1>
            </div>
            <div class="flex gap-2">
                <x-button href="{{ route('dashboard.crm.deals.create') }}">
                    <x-tabler-plus class="size-4" />
                    {{ __('New Deal') }}
                </x-button>
            </div>
        </div>

        <div class="flex gap-2">
            <button class="px-3 py-2 rounded-md text-sm font-semibold"
                    :class="mode === 'list' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'"
                    x-on:click="mode='list'">{{ __('List') }}</button>
            <button class="px-3 py-2 rounded-md text-sm font-semibold"
                    :class="mode === 'board' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'"
                    x-on:click="mode='board'">{{ __('Board') }}</button>
        </div>

        <div x-show="mode === 'list'" x-cloak>
            <x-table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('Deal') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Owner') }}</th>
                        <th>{{ __('Stage') }}</th>
                        <th class="text-end">{{ __('Value') }}</th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @foreach($deals as $deal)
                        <tr>
                            <td class="font-semibold">{{ $deal['title'] }}</td>
                            <td>{{ $deal['customer'] }}</td>
                            <td>{{ $deal['owner'] }}</td>
                            <td><x-badge variant="info">{{ ucfirst($deal['stage']) }}</x-badge></td>
                            <td class="text-end">${{ number_format($deal['value'], 2) }}</td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-table>
        </div>

        <div x-show="mode === 'board'" x-cloak class="grid md:grid-cols-3 gap-4">
            @foreach($grouped as $stage => $items)
                <div class="border rounded-lg bg-slate-50">
                    <div class="px-4 py-3 border-b flex items-center justify-between">
                        <div class="font-semibold">{{ ucfirst($stage) }}</div>
                        <div class="text-xs text-slate-500">{{ $items->count() }} {{ __('deals') }}</div>
                    </div>
                    <div class="p-3 space-y-3">
                        @foreach($items as $deal)
                            <div class="bg-white rounded-md p-3 shadow-sm">
                                <div class="font-semibold">{{ $deal['title'] }}</div>
                                <p class="text-sm text-slate-500">{{ $deal['customer'] }}</p>
                                <p class="text-xs text-slate-500 mt-1">{{ __('Owner') }}: {{ $deal['owner'] }}</p>
                                <p class="text-xs text-slate-500">{{ __('Value') }}: ${{ number_format($deal['value'], 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

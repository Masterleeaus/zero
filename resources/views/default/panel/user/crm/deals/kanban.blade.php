@extends('default.layout.app')
@section('content')
    <div class="max-w-6xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Deals') }}</p>
                <h1 class="text-2xl font-semibold">{{ __('Kanban Board') }}</h1>
            </div>
            <x-button href="{{ route('dashboard.crm.deals.index') }}" variant="ghost">
                <x-tabler-list class="size-4" />
                {{ __('List') }}
            </x-button>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            @foreach($columns as $stage => $items)
                <div class="border rounded-lg bg-slate-50">
                    <div class="px-4 py-3 border-b flex items-center justify-between">
                        <div class="font-semibold">{{ ucfirst($stage) }}</div>
                        <div class="text-xs text-slate-500">{{ $items->count() }} {{ __('deals') }}</div>
                    </div>
                    <div class="p-3 space-y-3">
                        @foreach($items as $deal)
                            <div class="bg-white rounded-md p-3 shadow-sm cursor-move">
                                <div class="flex items-center justify-between">
                                    <div class="font-semibold">{{ $deal['title'] }}</div>
                                    <x-badge variant="ghost">${{ number_format($deal['value'], 0) }}</x-badge>
                                </div>
                                <p class="text-sm text-slate-500">{{ $deal['customer'] }}</p>
                                <p class="text-xs text-slate-500 mt-1">{{ __('Owner') }}: {{ $deal['owner'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

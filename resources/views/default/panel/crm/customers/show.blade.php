@extends('panel.layout.app')
@section('title', __('Customer'))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.crm.customers.edit', $customer) }}">
        <x-tabler-pencil class="size-4" />
        {{ __('Edit') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <x-card>
            <div class="space-y-2">
                <div class="text-sm text-slate-500">{{ __('Name') }}</div>
                <div class="text-lg font-semibold">{{ $customer->name }}</div>
            </div>
            @if($customer->email)
                <div class="mt-4">
                    <div class="text-sm text-slate-500">{{ __('Email') }}</div>
                    <div>{{ $customer->email }}</div>
                </div>
            @endif
            @if($customer->phone)
                <div class="mt-4">
                    <div class="text-sm text-slate-500">{{ __('Phone') }}</div>
                    <div>{{ $customer->phone }}</div>
                </div>
            @endif
            <div class="mt-4">
                <div class="text-sm text-slate-500">{{ __('Status') }}</div>
                <x-badge variant="info">{{ ucfirst($customer->status) }}</x-badge>
            </div>
            @if($customer->notes)
                <div class="mt-4">
                    <div class="text-sm text-slate-500">{{ __('Notes') }}</div>
                    <p class="whitespace-pre-line">{{ $customer->notes }}</p>
                </div>
            @endif
        </x-card>
    </div>
@endsection

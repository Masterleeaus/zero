@extends('panel.layout.app')
@section('title', __('Enquiry'))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.crm.enquiries.create', ['customer_id' => $enquiry->customer_id]) }}">
        <x-tabler-plus class="size-4" />
        {{ __('New Enquiry') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <x-card>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-slate-500">{{ __('Name') }}</div>
                    <div class="text-lg font-semibold">{{ $enquiry->name }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Status') }}</div>
                    <x-badge variant="info">{{ ucfirst($enquiry->status) }}</x-badge>
                </div>
                @if($enquiry->customer)
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Customer') }}</div>
                        <div>{{ $enquiry->customer->name }}</div>
                    </div>
                @endif
                @if($enquiry->email)
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Email') }}</div>
                        <div>{{ $enquiry->email }}</div>
                    </div>
                @endif
                @if($enquiry->phone)
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Phone') }}</div>
                        <div>{{ $enquiry->phone }}</div>
                    </div>
                @endif
                @if($enquiry->source)
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Source') }}</div>
                        <div>{{ $enquiry->source }}</div>
                    </div>
                @endif
            </div>

            @if($enquiry->notes)
                <div class="mt-4">
                    <div class="text-sm text-slate-500">{{ __('Notes') }}</div>
                    <p class="whitespace-pre-line">{{ $enquiry->notes }}</p>
                </div>
            @endif
        </x-card>
    </div>
@endsection

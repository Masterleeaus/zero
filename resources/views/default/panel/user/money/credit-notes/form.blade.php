@extends('default.layout.app')
@section('content')
    <div class="max-w-4xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Credit Note') }}</p>
                <h1 class="text-2xl font-semibold">
                    {{ $creditNote ? __('Edit Credit Note') : __('New Credit Note') }}
                </h1>
            </div>
        </div>

        <x-card>
            <form class="space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <x-input name="number" label="{{ __('Credit Note #') }}" value="{{ $creditNote['number'] ?? '' }}" />
                    <x-input name="customer" label="{{ __('Customer') }}" value="{{ $creditNote['customer'] ?? '' }}" />
                    <x-select name="status" label="{{ __('Status') }}">
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(($creditNote['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </x-select>
                    <x-input name="issued_at" type="date" label="{{ __('Issue Date') }}" value="{{ $creditNote['issued_at'] ?? now()->toDateString() }}" />
                </div>

                <x-line-item-editor :items="$items" name="items" />

                <div class="flex gap-3">
                    <x-button type="submit">
                        <x-tabler-check class="size-4" />
                        {{ __('Save') }}
                    </x-button>
                    <x-button variant="ghost" href="{{ route('dashboard.money.credit-notes.index') }}">
                        {{ __('Cancel') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection


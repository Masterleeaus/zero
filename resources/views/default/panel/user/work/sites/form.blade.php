@extends('panel.layout.app')
@section('title', $site->exists ? __('Edit Site') : __('New Site'))

@section('content')
    <div class="py-6">
        <form method="post"
              action="{{ $site->exists ? route('dashboard.work.sites.update', $site) : route('dashboard.work.sites.store') }}"
              class="space-y-4">
            @csrf
            @if($site->exists)
                @method('put')
            @endif

            <div class="grid md:grid-cols-2 gap-4">
                <x-input label="{{ __('Name') }}" name="name" required value="{{ old('name', $site->name) }}" />
                <x-input label="{{ __('Reference') }}" name="reference" value="{{ old('reference', $site->reference) }}" />
                <x-input label="{{ __('Address') }}" name="address" value="{{ old('address', $site->address) }}" />
                <x-input label="{{ __('Status') }}" name="status" value="{{ old('status', $site->status) }}" />
                <x-input label="{{ __('Start Date') }}" type="date" name="start_date" value="{{ old('start_date', optional($site->start_date)->format('Y-m-d')) }}" />
                <x-input label="{{ __('Deadline') }}" type="date" name="deadline" value="{{ old('deadline', optional($site->deadline)->format('Y-m-d')) }}" />
            </div>

            <x-textarea label="{{ __('Notes') }}" name="notes" rows="4">{{ old('notes', $site->notes) }}</x-textarea>

            <div class="flex gap-3">
                <x-button type="submit">
                    <x-tabler-check class="size-4" />
                    {{ $site->exists ? __('Update') : __('Create') }}
                </x-button>
                <x-button type="button" href="{{ $site->exists ? route('dashboard.work.sites.show', $site) : route('dashboard.work.sites.index') }}" variant="secondary">
                    {{ __('Cancel') }}
                </x-button>
            </div>
        </form>
    </div>
@endsection

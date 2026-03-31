@extends('panel.layout.app')
@section('title', $type->exists ? __('Edit Job Type') : __('New Job Type'))

@section('content')
    <div class="py-6">
        <form method="post"
              action="{{ $type->exists ? route('dashboard.work.job-types.update', $type) : route('dashboard.work.job-types.store') }}"
              class="space-y-4">
            @csrf
            @if($type->exists)
                @method('put')
            @endif

            <x-card>
                <x-input label="{{ __('Name') }}" name="name" required value="{{ old('name', $type->name) }}" />
            </x-card>

            <div class="flex gap-3">
                <x-button type="submit">
                    <x-tabler-check class="size-4" />
                    {{ $type->exists ? __('Update') : __('Create') }}
                </x-button>
                <x-button type="button"
                          href="{{ $type->exists ? route('dashboard.work.job-types.show', $type) : route('dashboard.work.job-types.index') }}"
                          variant="secondary">
                    {{ __('Cancel') }}
                </x-button>
            </div>
        </form>
    </div>
@endsection

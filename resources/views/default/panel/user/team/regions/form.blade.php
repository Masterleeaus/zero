@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Region') }}</p>
                <h1 class="text-2xl font-semibold">
                    {{ $region->exists ? __('Edit Region') : __('Add Region') }}
                </h1>
            </div>
        </div>

        <x-card>
            <form method="post"
                  action="{{ $region->exists ? route('dashboard.team.regions.update', $region) : route('dashboard.team.regions.store') }}"
                  class="space-y-4">
                @csrf
                @if($region->exists)
                    @method('put')
                @endif

                <x-input name="name" label="{{ __('Name') }}" value="{{ old('name', $region->name) }}" required />
                <x-input name="description" label="{{ __('Description') }}" value="{{ old('description', $region->description) }}" />

                <div>
                    <label class="form-label">{{ __('Manager') }}</label>
                    <x-select name="manager_user_id">
                        <option value="">{{ __('— None —') }}</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}" @selected(old('manager_user_id', $region->manager_user_id) == $manager->id)>
                                {{ $manager->name }}
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div class="flex gap-3">
                    <x-button type="submit">
                        <x-tabler-check class="size-4" />
                        {{ $region->exists ? __('Update') : __('Create') }}
                    </x-button>
                    <x-button variant="ghost" href="{{ route('dashboard.team.regions.index') }}">
                        {{ __('Cancel') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection

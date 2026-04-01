@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('District') }}</p>
                <h1 class="text-2xl font-semibold">
                    {{ $district->exists ? __('Edit District') : __('Add District') }}
                </h1>
            </div>
        </div>

        <x-card>
            <form method="post"
                  action="{{ $district->exists ? route('dashboard.team.districts.update', $district) : route('dashboard.team.districts.store') }}"
                  class="space-y-4">
                @csrf
                @if($district->exists)
                    @method('put')
                @endif

                <x-input name="name" label="{{ __('Name') }}" value="{{ old('name', $district->name) }}" required />
                <x-input name="description" label="{{ __('Description') }}" value="{{ old('description', $district->description) }}" />

                <div>
                    <label class="form-label">{{ __('Region') }}</label>
                    <x-select name="region_id">
                        <option value="">{{ __('— None —') }}</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->id }}" @selected(old('region_id', $district->region_id) == $region->id)>
                                {{ $region->name }}
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <label class="form-label">{{ __('Manager') }}</label>
                    <x-select name="manager_user_id">
                        <option value="">{{ __('— None —') }}</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}" @selected(old('manager_user_id', $district->manager_user_id) == $manager->id)>
                                {{ $manager->name }}
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div class="flex gap-3">
                    <x-button type="submit">
                        <x-tabler-check class="size-4" />
                        {{ $district->exists ? __('Update') : __('Create') }}
                    </x-button>
                    <x-button variant="ghost" href="{{ route('dashboard.team.districts.index') }}">
                        {{ __('Cancel') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection

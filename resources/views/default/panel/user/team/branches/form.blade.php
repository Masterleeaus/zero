@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Branch') }}</p>
                <h1 class="text-2xl font-semibold">
                    {{ $branch->exists ? __('Edit Branch') : __('Add Branch') }}
                </h1>
            </div>
        </div>

        <x-card>
            <form method="post"
                  action="{{ $branch->exists ? route('dashboard.team.branches.update', $branch) : route('dashboard.team.branches.store') }}"
                  class="space-y-4">
                @csrf
                @if($branch->exists)
                    @method('put')
                @endif

                <x-input name="name" label="{{ __('Name') }}" value="{{ old('name', $branch->name) }}" required />
                <x-input name="description" label="{{ __('Description') }}" value="{{ old('description', $branch->description) }}" />

                <div>
                    <label class="form-label">{{ __('District') }}</label>
                    <x-select name="district_id">
                        <option value="">{{ __('— None —') }}</option>
                        @foreach($districts as $district)
                            <option value="{{ $district->id }}" @selected(old('district_id', $branch->district_id) == $district->id)>
                                {{ $district->name }}
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <label class="form-label">{{ __('Manager') }}</label>
                    <x-select name="manager_user_id">
                        <option value="">{{ __('— None —') }}</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}" @selected(old('manager_user_id', $branch->manager_user_id) == $manager->id)>
                                {{ $manager->name }}
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div class="flex gap-3">
                    <x-button type="submit">
                        <x-tabler-check class="size-4" />
                        {{ $branch->exists ? __('Update') : __('Create') }}
                    </x-button>
                    <x-button variant="ghost" href="{{ route('dashboard.team.branches.index') }}">
                        {{ __('Cancel') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection

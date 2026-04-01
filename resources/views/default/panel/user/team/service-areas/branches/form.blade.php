@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Branch') }}</p>
                <h1 class="text-2xl font-semibold">{{ $branch ? __('Edit Branch') : __('Add Branch') }}</h1>
            </div>
        </div>

        <x-card>
            <form method="POST"
                  action="{{ $branch ? route('dashboard.team.service-area-branches.update', $branch) : route('dashboard.team.service-area-branches.store') }}"
                  class="space-y-4">
                @csrf
                @if($branch)
                    @method('PUT')
                @endif

                <x-input name="name" label="{{ __('Name') }}" value="{{ old('name', $branch?->name) }}" required />
                <x-input name="description" label="{{ __('Description') }}" value="{{ old('description', $branch?->description) }}" />

                @if($districts->isNotEmpty())
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('District') }}</label>
                        <select name="district_id" class="w-full border-slate-300 rounded-md shadow-sm text-sm">
                            <option value="">— {{ __('None') }} —</option>
                            @foreach($districts as $district)
                                <option value="{{ $district->id }}" @selected(old('district_id', $branch?->district_id) == $district->id)>
                                    {{ $district->name }}{{ $district->region ? ' (' . $district->region->name . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex gap-3">
                    <x-button type="submit">
                        <x-tabler-check class="size-4" />
                        {{ __('Save') }}
                    </x-button>
                    <x-button variant="ghost" href="{{ route('dashboard.team.service-area-branches.index') }}">
                        {{ __('Cancel') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection

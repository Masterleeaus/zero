@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Territory') }}</p>
                <h1 class="text-2xl font-semibold">
                    {{ $territory->exists ? __('Edit Territory') : __('Add Territory') }}
                </h1>
            </div>
        </div>

        <x-card>
            <form method="POST"
                  action="{{ $zone ? route('dashboard.team.zones.update', $zone) : route('dashboard.team.zones.store') }}"
                  class="space-y-4">
                @csrf
                @if($zone)
                    @method('PUT')
                @endif

                <x-input name="name" label="{{ __('Name') }}" value="{{ old('name', $zone?->name) }}" required />
                <x-input name="code" label="{{ __('Code') }}" value="{{ old('code', $zone?->code) }}" />
                <x-input name="description" label="{{ __('Description') }}" value="{{ old('description', $zone?->description) }}" />

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Type') }}</label>
                    <select name="type" class="w-full border-slate-300 rounded-md shadow-sm text-sm">
                        <option value="">— {{ __('None') }} —</option>
                        @foreach(['zip' => __('ZIP / Postcode'), 'suburb' => __('Suburb'), 'state' => __('State')] as $val => $label)
                            <option value="{{ $val }}" @selected(old('type', $zone?->type) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <x-input name="zip_codes" label="{{ __('ZIP / Postcode Coverage') }}" value="{{ old('zip_codes', $zone?->zip_codes) }}" />

                @if($branches->isNotEmpty())
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Branch') }}</label>
                        <select name="branch_id" class="w-full border-slate-300 rounded-md shadow-sm text-sm">
                            <option value="">— {{ __('None') }} —</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(old('branch_id', $zone?->branch_id) == $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex gap-3">
                    <x-button type="submit">
                        <x-tabler-check class="size-4" />
                        {{ $territory->exists ? __('Update') : __('Create') }}
                    </x-button>
                    <x-button variant="ghost" href="{{ route('dashboard.team.zones.index') }}">
                        {{ __('Cancel') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection

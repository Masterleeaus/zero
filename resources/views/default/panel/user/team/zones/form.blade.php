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
            <form method="post"
                  action="{{ $territory->exists ? route('dashboard.team.zones.update', $territory) : route('dashboard.team.zones.store') }}"
                  class="space-y-4">
                @csrf
                @if($territory->exists)
                    @method('put')
                @endif

                <x-input name="name" label="{{ __('Name') }}" value="{{ old('name', $territory->name) }}" required />
                <x-input name="description" label="{{ __('Description') }}" value="{{ old('description', $territory->description) }}" />

                <div>
                    <label class="form-label">{{ __('Branch') }}</label>
                    <x-select name="branch_id">
                        <option value="">{{ __('— None —') }}</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(old('branch_id', $territory->branch_id) == $branch->id)>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <label class="form-label">{{ __('Type') }}</label>
                    <x-select name="type">
                        <option value="">{{ __('— None —') }}</option>
                        @foreach(['zip', 'state', 'country'] as $type)
                            <option value="{{ $type }}" @selected(old('type', $territory->type) === $type)>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <label class="form-label">{{ __('ZIP Codes') }}</label>
                    <x-textarea name="zip_codes" rows="3" placeholder="{{ __('Comma-separated ZIP codes') }}">{{ old('zip_codes', $territory->zip_codes) }}</x-textarea>
                </div>

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

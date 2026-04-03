@extends('panel.layout.app')
@section('title', $route ? __('Edit Route') : __('New Route'))

@section('content')
    <div class="py-6 max-w-2xl">
        <form method="POST"
              action="{{ $route ? route('dashboard.work.routes.update', $route) : route('dashboard.work.routes.store') }}">
            @csrf
            @if($route)
                @method('PUT')
            @endif

            <div class="space-y-4">

                <x-input name="name" label="{{ __('Route Name') }}" value="{{ old('name', $route?->name) }}" required />

                <x-select name="assigned_user_id" label="{{ __('Default Technician') }}">
                    <option value="">{{ __('— None —') }}</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(old('assigned_user_id', $route?->assigned_user_id) == $user->id)>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </x-select>

                <x-select name="team_id" label="{{ __('Team') }}">
                    <option value="">{{ __('— None —') }}</option>
                    @foreach($teams as $team)
                        <option value="{{ $team->id }}" @selected(old('team_id', $route?->team_id) == $team->id)>
                            {{ $team->name }}
                        </option>
                    @endforeach
                </x-select>

                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('Active Days') }}</label>
                    <div class="flex flex-wrap gap-3">
                        @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $i => $day)
                            @php $bit = 1 << $i; $mask = old('active_days_mask', $route?->active_days_mask ?? 0b0011111); @endphp
                            <label class="flex items-center gap-1 text-sm">
                                <input type="checkbox" name="active_days_mask_bits[]" value="{{ $bit }}"
                                       @checked($mask & $bit)
                                       class="rounded" />
                                {{ $day }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <x-input name="max_stops_per_day" type="number" min="0"
                          label="{{ __('Max Stops Per Day (0 = unlimited)') }}"
                          value="{{ old('max_stops_per_day', $route?->max_stops_per_day ?? 0) }}" />

                @if($territories->count())
                    <x-select name="territory_id" label="{{ __('Territory') }}">
                        <option value="">{{ __('— None —') }}</option>
                        @foreach($territories as $territory)
                            <option value="{{ $territory->id }}" @selected(old('territory_id', $route?->territory_id) == $territory->id)>
                                {{ $territory->name }}
                            </option>
                        @endforeach
                    </x-select>
                @endif

                <x-select name="status" label="{{ __('Status') }}">
                    @foreach($statuses as $option)
                        <option value="{{ $option }}" @selected(old('status', $route?->status ?? 'active') === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </x-select>

                <x-textarea name="notes" label="{{ __('Notes') }}" rows="3">{{ old('notes', $route?->notes) }}</x-textarea>

            </div>

            <div class="mt-6 flex gap-3">
                <x-button type="submit">{{ $route ? __('Update Route') : __('Create Route') }}</x-button>
                <x-button href="{{ $route ? route('dashboard.work.routes.show', $route) : route('dashboard.work.routes.index') }}" variant="ghost">
                    {{ __('Cancel') }}
                </x-button>
            </div>

        </form>
    </div>
@endsection

@push('scripts')
<script>
    // Aggregate checkbox bits into the hidden active_days_mask field on submit.
    document.querySelector('form').addEventListener('submit', function () {
        const checkboxes = document.querySelectorAll('[name="active_days_mask_bits[]"]');
        let mask = 0;
        checkboxes.forEach(cb => { if (cb.checked) mask |= parseInt(cb.value); });
        const hidden = document.createElement('input');
        hidden.type  = 'hidden';
        hidden.name  = 'active_days_mask';
        hidden.value = mask;
        this.appendChild(hidden);
    });
</script>
@endpush

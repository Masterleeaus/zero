@extends('panel.layout.app')
@section('title', $availability ? __('Edit Availability') : __('New Availability Schedule'))

@section('content')
    <div class="py-6 max-w-2xl">
        <form method="POST"
              action="{{ $availability ? route('dashboard.work.routes.availability.update', $availability) : route('dashboard.work.routes.availability.store') }}">
            @csrf
            @if($availability)
                @method('PUT')
            @endif

            <div class="space-y-4">

                <x-select name="user_id" label="{{ __('Technician') }}" required>
                    <option value="">{{ __('— Select —') }}</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(old('user_id', $availability?->user_id) == $user->id)>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </x-select>

                <x-select name="team_id" label="{{ __('Team') }}">
                    <option value="">{{ __('— None —') }}</option>
                    @foreach($teams as $team)
                        <option value="{{ $team->id }}" @selected(old('team_id', $availability?->team_id) == $team->id)>
                            {{ $team->name }}
                        </option>
                    @endforeach
                </x-select>

                <x-input name="name" label="{{ __('Schedule Name') }}"
                          value="{{ old('name', $availability?->name) }}"
                          placeholder="{{ __('e.g. Standard Week') }}" />

                <div class="grid grid-cols-2 gap-4">
                    <x-input name="work_start_time" type="time" label="{{ __('Work Start') }}"
                              value="{{ old('work_start_time', $availability?->work_start_time) }}" />
                    <x-input name="work_end_time" type="time" label="{{ __('Work End') }}"
                              value="{{ old('work_end_time', $availability?->work_end_time) }}" />
                </div>

                <x-input name="max_work_hours" type="number" step="0.5" min="0" max="24"
                          label="{{ __('Max Work Hours/Day') }}"
                          value="{{ old('max_work_hours', $availability?->max_work_hours) }}" />

                <div class="flex items-center gap-2">
                    <input type="hidden" name="overtime_allowed" value="0" />
                    <input type="checkbox" id="overtime_allowed" name="overtime_allowed" value="1"
                           @checked(old('overtime_allowed', $availability?->overtime_allowed)) class="rounded" />
                    <label for="overtime_allowed" class="text-sm">{{ __('Overtime Allowed') }}</label>
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0" />
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                           @checked(old('is_active', $availability?->is_active ?? true)) class="rounded" />
                    <label for="is_active" class="text-sm">{{ __('Active') }}</label>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <x-input name="valid_from" type="date" label="{{ __('Valid From') }}"
                              value="{{ old('valid_from', $availability?->valid_from) }}" />
                    <x-input name="valid_until" type="date" label="{{ __('Valid Until') }}"
                              value="{{ old('valid_until', $availability?->valid_until) }}" />
                </div>

                <x-textarea name="notes" label="{{ __('Notes') }}" rows="3">{{ old('notes', $availability?->notes) }}</x-textarea>

            </div>

            <div class="mt-6 flex gap-3">
                <x-button type="submit">
                    {{ $availability ? __('Update Availability') : __('Create Availability') }}
                </x-button>
                <x-button href="{{ route('dashboard.work.routes.availability.index') }}" variant="ghost">
                    {{ __('Cancel') }}
                </x-button>
            </div>

        </form>
    </div>
@endsection

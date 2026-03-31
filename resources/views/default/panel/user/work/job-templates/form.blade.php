@extends('panel.layout.app')
@section('title', $template->exists ? __('Edit Job Template') : __('New Job Template'))

@section('content')
    <div class="py-6">
        <form method="post"
              action="{{ $template->exists ? route('dashboard.work.job-templates.update', $template) : route('dashboard.work.job-templates.store') }}"
              class="space-y-4">
            @csrf
            @if($template->exists)
                @method('put')
            @endif

            <x-card>
                <div class="grid md:grid-cols-2 gap-4">
                    <x-input label="{{ __('Name') }}" name="name" required value="{{ old('name', $template->name) }}" />

                    <x-select label="{{ __('Job Type') }}" name="job_type_id">
                        <option value="">{{ __('— None —') }}</option>
                        @foreach($jobTypes as $jt)
                            <option value="{{ $jt->id }}" @selected(old('job_type_id', $template->job_type_id) == $jt->id)>{{ $jt->name }}</option>
                        @endforeach
                    </x-select>

                    <x-select label="{{ __('Team') }}" name="team_id">
                        <option value="">{{ __('— None —') }}</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('team_id', $template->team_id) == $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </x-select>

                    <x-input label="{{ __('Default Duration (hours)') }}" type="number" name="duration" step="0.25" min="0"
                             value="{{ old('duration', $template->duration ?? 0) }}" />
                </div>

                <div class="mt-4">
                    <x-textarea label="{{ __('Instructions') }}" name="instructions" rows="5">{{ old('instructions', $template->instructions) }}</x-textarea>
                </div>
            </x-card>

            <div class="flex gap-3">
                <x-button type="submit">
                    <x-tabler-check class="size-4" />
                    {{ $template->exists ? __('Update') : __('Create') }}
                </x-button>
                <x-button type="button"
                          href="{{ $template->exists ? route('dashboard.work.job-templates.show', $template) : route('dashboard.work.job-templates.index') }}"
                          variant="secondary">
                    {{ __('Cancel') }}
                </x-button>
            </div>
        </form>
    </div>
@endsection

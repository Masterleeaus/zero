@extends('panel.layout.app')
@section('title', $checklist->exists ? __('work.checklists.edit') : __('work.checklists.new'))

@section('content')
    <div class="py-6">
        <form method="post"
              action="{{ $checklist->exists ? route('dashboard.work.checklists.update', $checklist) : route('dashboard.work.checklists.store') }}"
              class="space-y-4">
            @csrf
            @if($checklist->exists)
                @method('put')
            @endif

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">{{ __('work.labels.service_job') }}</label>
                    <x-select name="service_job_id" required>
                        <option value="">{{ __('work.jobs.select_job') }}</option>
                        @foreach($jobs as $job)
                            <option value="{{ $job->id }}" @selected(old('service_job_id', $jobId ?? $checklist->service_job_id) == $job->id)>
                                {{ $job->title }}
                            </option>
                        @endforeach
                    </x-select>
                </div>
                <x-input label="{{ __('Title') }}" name="title" required value="{{ old('title', $checklist->title) }}" />
                <label class="flex items-center gap-2 mt-2">
                    <input type="checkbox" name="is_completed" value="1" @checked(old('is_completed', $checklist->is_completed))>
                    <span>{{ __('Completed') }}</span>
                </label>
            </div>

            <x-textarea label="{{ __('Notes') }}" name="notes" rows="4">{{ old('notes', $checklist->notes) }}</x-textarea>

            <div class="flex gap-3">
                <x-button type="submit">
                    <x-tabler-check class="size-4" />
                    {{ $checklist->exists ? __('Update') : __('Create') }}
                </x-button>
                <x-button type="button" href="{{ $checklist->exists ? route('dashboard.work.checklists.show', $checklist) : route('dashboard.work.checklists.index') }}" variant="secondary">
                    {{ __('Cancel') }}
                </x-button>
            </div>
        </form>
    </div>
@endsection

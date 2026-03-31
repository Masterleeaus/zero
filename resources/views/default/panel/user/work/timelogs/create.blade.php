@extends('panel.layout.app')
@section('title', __('Start timelog'))

@section('content')
    <div class="py-6 space-y-4">
        <h2 class="text-lg font-semibold">{{ __('Start a new timelog') }}</h2>

        <form method="post" action="{{ route('dashboard.work.timelogs.store') }}" class="space-y-4">
            @csrf
            <x-form.group>
                <x-form.label for="service_job_id">{{ __('work.labels.service_job') }} ({{ __('optional') }})</x-form.label>
                <x-form.select name="service_job_id" id="service_job_id">
                    <option value="">{{ __('work.jobs.select_job') }}</option>
                    @foreach($jobs as $job)
                        <option value="{{ $job->id }}">{{ $job->title }}</option>
                    @endforeach
                </x-form.select>
            </x-form.group>

            <x-form.group>
                <x-form.label for="started_at">{{ __('Started at') }}</x-form.label>
                <x-form.input type="datetime-local" name="started_at" id="started_at" value="{{ now()->format('Y-m-d\TH:i') }}" required />
            </x-form.group>

            <x-form.group>
                <x-form.label for="notes">{{ __('Notes') }}</x-form.label>
                <x-form.textarea name="notes" id="notes" rows="3"></x-form.textarea>
            </x-form.group>

            <x-button type="submit">{{ __('Start') }}</x-button>
        </form>
    </div>
@endsection

@extends('panel.layout.app')
@section('title', __('Check in'))

@section('content')
    <div class="py-6 space-y-4">
        <h2 class="text-lg font-semibold">{{ __('New check-in') }}</h2>

        <form method="post" action="{{ route('dashboard.work.attendance.store') }}" class="space-y-4">
            @csrf
            <x-form.group>
                <x-form.label for="service_job_id">{{ __('Service Job (optional)') }}</x-form.label>
                <x-form.select name="service_job_id" id="service_job_id">
                    <option value="">{{ __('Select a job') }}</option>
                    @foreach($jobs as $job)
                        <option value="{{ $job->id }}">{{ $job->title }}</option>
                    @endforeach
                </x-form.select>
            </x-form.group>

            <x-form.group>
                <x-form.label for="check_in_at">{{ __('Check in time') }}</x-form.label>
                <x-form.input type="datetime-local" name="check_in_at" id="check_in_at" value="{{ now()->format('Y-m-d\TH:i') }}" required />
            </x-form.group>

            <x-form.group>
                <x-form.label for="notes">{{ __('Notes') }}</x-form.label>
                <x-form.textarea name="notes" id="notes" rows="3"></x-form.textarea>
            </x-form.group>

            <x-button type="submit">{{ __('Check in') }}</x-button>
        </form>
    </div>
@endsection

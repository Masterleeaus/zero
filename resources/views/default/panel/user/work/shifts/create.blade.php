@extends('panel.layout.app')
@section('title', __('Create Shift'))

@section('content')
    <div class="py-6 space-y-4 max-w-3xl">
        <h1 class="text-xl font-semibold">{{ __('Create Shift') }}</h1>
        <x-card>
            <form method="post" action="{{ route('dashboard.work.shifts.store') }}" class="space-y-4">
                @csrf
                <div class="grid md:grid-cols-2 gap-4">
                    <x-form.select name="user_id" label="{{ __('User') }}">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </x-form.select>
                    <x-form.select name="service_job_id" label="{{ __('Service Job (optional)') }}">
                        <option value="">{{ __('None') }}</option>
                        @foreach($jobs as $job)
                            <option value="{{ $job->id }}">{{ $job->title }}</option>
                        @endforeach
                    </x-form.select>
                    <x-form.input type="datetime-local" name="start_at" label="{{ __('Start At') }}" required />
                    <x-form.input type="datetime-local" name="end_at" label="{{ __('End At') }}" required />
                </div>
                <x-form.input type="text" name="status" label="{{ __('Status') }}" value="scheduled" />
                <div class="flex justify-end">
                    <x-button type="submit">{{ __('Save') }}</x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection

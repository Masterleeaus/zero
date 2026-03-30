@extends('panel.layout.app')
@section('title', $job->exists ? __('Edit Service Job') : __('New Service Job'))

@section('content')
    <div class="py-6">
        <form method="post"
              action="{{ $job->exists ? route('dashboard.work.service-jobs.update', $job) : route('dashboard.work.service-jobs.store') }}"
              class="space-y-4">
            @csrf
            @if($job->exists)
                @method('put')
            @endif

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">{{ __('Site') }}</label>
                    <x-select name="site_id" required>
                        <option value="">{{ __('Select site') }}</option>
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}" @selected(old('site_id', $siteId ?? $job->site_id) == $site->id)>
                                {{ $site->name }}
                            </option>
                        @endforeach
                    </x-select>
                </div>
                <div>
                    <label class="form-label">{{ __('Customer') }}</label>
                    <x-select name="customer_id">
                        <option value="">{{ __('Select customer') }}</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id', $job->customer_id) == $customer->id)>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </x-select>
                </div>
                <div>
                    <label class="form-label">{{ __('Assignee') }}</label>
                    <x-select name="assigned_user_id">
                        <option value="">{{ __('Unassigned') }}</option>
                        @foreach($assignees as $assignee)
                            <option value="{{ $assignee->id }}" @selected(old('assigned_user_id', $job->assigned_user_id) == $assignee->id)>
                                {{ $assignee->name }}
                            </option>
                        @endforeach
                    </x-select>
                </div>
                <x-input label="{{ __('Title') }}" name="title" required value="{{ old('title', $job->title) }}" />
                <div>
                    <label class="form-label">{{ __('Status') }}</label>
                    <x-select name="status">
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(old('status', $job->status ?? 'scheduled') === $status)>{{ ucfirst(str_replace('_',' ',$status)) }}</option>
                        @endforeach
                    </x-select>
                </div>
                <x-input label="{{ __('Scheduled At') }}" type="datetime-local" name="scheduled_at"
                         value="{{ old('scheduled_at', optional($job->scheduled_at)->format('Y-m-d\\TH:i')) }}" />
            </div>

            <x-textarea label="{{ __('Notes') }}" name="notes" rows="4">{{ old('notes', $job->notes) }}</x-textarea>

            <div class="flex gap-3">
                <x-button type="submit">
                    <x-tabler-check class="size-4" />
                    {{ $job->exists ? __('Update') : __('Create') }}
                </x-button>
                <x-button type="button" href="{{ $job->exists ? route('dashboard.work.service-jobs.show', $job) : route('dashboard.work.service-jobs.index') }}" variant="secondary">
                    {{ __('Cancel') }}
                </x-button>
            </div>
        </form>
    </div>
@endsection

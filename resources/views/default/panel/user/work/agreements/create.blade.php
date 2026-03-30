@extends('panel.layout.app')
@section('title', __('Create Agreement'))

@section('content')
    <div class="py-6 space-y-4">
        <h2 class="text-lg font-semibold">{{ __('New Service Agreement') }}</h2>
        <form method="post" action="{{ route('dashboard.work.agreements.store') }}" class="space-y-4">
            @csrf
            <x-form.group>
                <x-form.label for="title">{{ __('Title') }}</x-form.label>
                <x-form.input type="text" name="title" id="title" required />
            </x-form.group>

            <x-form.group>
                <x-form.label for="customer_id">{{ __('Customer') }}</x-form.label>
                <x-form.select name="customer_id" id="customer_id">
                    <option value="">{{ __('Select customer') }}</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </x-form.select>
            </x-form.group>

            <x-form.group>
                <x-form.label for="site_id">{{ __('Site') }}</x-form.label>
                <x-form.select name="site_id" id="site_id">
                    <option value="">{{ __('Select site') }}</option>
                    @foreach($sites as $site)
                        <option value="{{ $site->id }}">{{ $site->name }}</option>
                    @endforeach
                </x-form.select>
            </x-form.group>

            <x-form.group>
                <x-form.label for="frequency">{{ __('Frequency') }}</x-form.label>
                <x-form.input type="text" name="frequency" id="frequency" value="monthly" required />
            </x-form.group>

            <x-form.group>
                <x-form.label for="next_run_at">{{ __('Next Run At') }}</x-form.label>
                <x-form.input type="date" name="next_run_at" id="next_run_at" />
            </x-form.group>

            <x-form.group>
                <x-form.label for="status">{{ __('Status') }}</x-form.label>
                <x-form.input type="text" name="status" id="status" value="active" required />
            </x-form.group>

            <x-button type="submit">{{ __('Create') }}</x-button>
        </form>
    </div>
@endsection

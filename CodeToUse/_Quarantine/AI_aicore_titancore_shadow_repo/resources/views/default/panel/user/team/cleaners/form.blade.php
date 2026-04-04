@extends('default.layout.app')
@section('content')
    <div class="max-w-3xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Cleaner') }}</p>
                <h1 class="text-2xl font-semibold">{{ __('Edit Profile') }}</h1>
            </div>
        </div>

        <x-card>
            <form class="space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <x-input name="name" label="{{ __('Name') }}" value="{{ $cleaner['name'] }}" />
                    <x-input name="role" label="{{ __('Role') }}" value="{{ $cleaner['role'] }}" />
                    <x-input name="email" label="{{ __('Email') }}" value="{{ $cleaner['email'] }}" />
                    <x-input name="phone" label="{{ __('Phone') }}" value="{{ $cleaner['phone'] }}" />
                </div>
                <x-input name="zones" label="{{ __('Zones') }}" value="{{ implode(', ', $cleaner['zones']) }}" />
                <x-input name="certifications" label="{{ __('Certifications') }}" value="{{ implode(', ', $cleaner['certifications']) }}" />
                <x-textarea name="bio" label="{{ __('Bio') }}">{{ $cleaner['bio'] }}</x-textarea>

                <div class="flex gap-3">
                    <x-button type="submit">
                        <x-tabler-check class="size-4" />
                        {{ __('Save') }}
                    </x-button>
                    <x-button variant="ghost" href="{{ route('dashboard.team.cleaners.show', $userId) }}">
                        {{ __('Cancel') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection

@extends('default.layout.app')
@section('content')
    <div class="max-w-4xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Cleaner') }}</p>
                <h1 class="text-2xl font-semibold">{{ $cleaner['name'] }}</h1>
                <p class="text-slate-500">{{ $cleaner['role'] }}</p>
            </div>
            <x-button href="{{ route('dashboard.team.cleaners.edit', $userId) }}">
                <x-tabler-pencil class="size-4" />
                {{ __('Edit') }}
            </x-button>
        </div>

        <x-card>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-slate-500">{{ __('Email') }}</p>
                    <p class="font-semibold">{{ $cleaner['email'] }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">{{ __('Phone') }}</p>
                    <p class="font-semibold">{{ $cleaner['phone'] }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">{{ __('Zones') }}</p>
                    <p class="font-semibold">{{ implode(', ', $cleaner['zones']) }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">{{ __('Certifications') }}</p>
                    <p class="font-semibold">{{ implode(', ', $cleaner['certifications']) }}</p>
                </div>
            </div>
            <p class="text-sm text-slate-600 mt-4">{{ $cleaner['bio'] }}</p>
        </x-card>
    </div>
@endsection
